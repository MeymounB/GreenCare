<?php

namespace App\Controller;

use AllowDynamicProperties;
use App\Entity\Appointment;
use App\Entity\Botanist;
use App\Entity\Particular;
use App\Entity\User;
use App\Form\AppointmentType;
use App\Repository\AppointmentRepository;
use App\Repository\PlantRepository;
use App\Repository\StatusRepository;
use App\Service\AppointmentService;
use App\Service\LinkManagerService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[AllowDynamicProperties] #[Route('/appointment')]
class AppointmentController extends AbstractController
{
    private AppointmentService $appointmentService;

    public function __construct(AppointmentService $appointmentService, PlantRepository $plantRepository)
    {
        $this->appointmentService = $appointmentService;
        $this->plantRepository = $plantRepository;
    }

    #[Route('/', name: 'app_appointment_index', methods: ['GET'])]
    public function index(): Response
    {
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw $this->createAccessDeniedException('Access denied');
        }

        $groupedAppointments = $this->appointmentService->getGroupedAppointmentsByParticular($user->getId());

        return $this->render('appointment/index.html.twig', [
            'groupedAppointments' => $groupedAppointments,
        ]);
    }

    #[Route('/new', name: 'app_appointment_new', methods: ['GET', 'POST'])]
    public function new(#[CurrentUser] ?Particular $user, Request $request, AppointmentRepository $appointmentRepository, StatusRepository $statusRepository, LinkManagerService $linkCreatorService): Response
    {
        $appointment = new Appointment();

        $defaultStatus = $statusRepository->findOneBy(['name' => 'En attente']);
        if (null !== $defaultStatus) {
            $appointment->setStatus($defaultStatus);
        }

        $user = $this->getUser();

        if (!$user instanceof User) {
            throw $this->createAccessDeniedException('Access denied');
        }

        $form = $this->createForm(AppointmentType::class, $appointment, [
            'plants' => $this->plantRepository->findBy(['particular' => $user->getId()]),
            'address' => [...$user->getAddress()],
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $isPresential = $form->get('isPresential')->getData();
            $address = $form->get('address')->getData();
            $plannedDate = $form->get('plannedAt')->getData();
            $plants = $form->get('plants')->getData();

            if ($plants) {
                foreach ($plants as $plant) {
                    $appointment->addPlant($plant);
                }
            }

            if ($isPresential) {
                if (is_null($address)) {
                    $form->addError(
                        new FormError("L'adresse est obligatoire pour un rendez-vous physique")
                    );
                } else {
                    $appointment->setAddress($address);
                }
            } else {
                $link = $linkCreatorService->createLink(
                    [
                        'endDate' => $plannedDate
                            ->add(\DateInterval::createFromDateString('1 day'))
                            ->format("Y-m-d\TH:i:s\Z"),
                    ]
                );
                $appointment->setLink($link);
            }

            $appointment->setParticular($this->getUser());
            $appointmentRepository->save($appointment, true);

            return $this->redirectToRoute('app_appointment_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('appointment/new.html.twig', [
            'appointment' => $appointment,
            'form' => $form,
            'error' => $form->getErrors()->current(),
        ]);
    }

    #[Route('/{id}/edit', name: 'app_appointment_edit', methods: ['GET', 'POST'])]
    public function edit(#[CurrentUser] ?Particular $user, Request $request, int $id, AppointmentService $service, AppointmentRepository $appointmentRepository): Response
    {
        $appointment = $service->getById($id);

        $form = $this->createForm(AppointmentType::class, $appointment, [
            'plants' => [...$user->getPlants()],
            'address' => [...$user->getAddress()],
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $appointmentRepository->save($appointment, true);

            return $this->redirectToRoute('app_appointment_show', ['id' => $appointment->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('appointment/edit.html.twig', [
            'appointment' => $appointment,
            'form' => $form,
            'error' => $form->getErrors()->current(),
        ]);
    }

    #[Route('/{id}', name: 'app_appointment_delete', methods: ['POST'])]
    public function delete(Request $request, int $id, AppointmentService $service, AppointmentRepository $appointmentRepository): Response
    {
        $appointment = $service->getById($id);

        //        if ($this->isCsrfTokenValid('delete'.$appointment->getId(), $request->request->get('_token'))) {
        $appointmentRepository->remove($appointment, true);
        //        }

        return $this->redirectToRoute('app_appointment_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/incoming_appointment', name: 'app_appointement_incoming_appointment', methods: ['GET'])]
    public function incoming_appointment(): Response
    {
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw $this->createAccessDeniedException('Access denied');
        }

        $type = null;
        if ($user instanceof Botanist) {
            $type = 'Botanist';
        } elseif ($user instanceof Particular) {
            $type = 'Particular';
        }

        $groupedAppointments = $this->appointmentService->getGroupedAppointmentsByBotanist($user->getId(), $type);

        return $this->render('botanist/incoming_appointments.html.twig', [
            'groupedAppointments' => $groupedAppointments,
        ]);
    }

    #[Route('/{id}', name: 'app_appointment_show', methods: ['GET'])]
    public function show(int $id, AppointmentService $service): Response
    {
        $appointment = $service->getById($id);

        return $this->render('appointment/show.html.twig', [
            'appointment' => $appointment,
        ]);
    }
}

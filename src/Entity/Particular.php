<?php

namespace App\Entity;

use App\Repository\ParticularRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ParticularRepository::class)]
class Particular extends User
{
    #[ORM\OneToMany(mappedBy: 'particular', targetEntity: Plant::class, orphanRemoval: true)]
    private Collection $plants;

    #[ORM\OneToMany(mappedBy: 'particular', targetEntity: Request::class, orphanRemoval: true)]
    private Collection $requests;

    #[ORM\OneToMany(mappedBy: 'particular', targetEntity: Address::class, orphanRemoval: true)]
    private Collection $address;

    public function __construct()
    {
        parent::__construct();
        $this->plants = new ArrayCollection();
        $this->requests = new ArrayCollection();
        $this->roles[] = 'ROLE_PARTICULAR';
        $this->address = new ArrayCollection();
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function getRequestTypes(): string
    {
        $requestTypes = $this->requests->map(function (Request $request) {
            $class = get_class($request); // récupère le nom complet de la classe
            $parts = explode('\\', $class); // découpe le nom de la classe par les backslashes

            return end($parts); // retourne le dernier élément du tableau, soit le nom de la classe sans le namespace
        });

        return implode(', ', $requestTypes->toArray());
    }

    /**
     * @return Collection<int, Plant>
     */
    public function getPlants(): Collection
    {
        return $this->plants;
    }

    public function addPlant(Plant $plant): static
    {
        if (!$this->plants->contains($plant)) {
            $this->plants->add($plant);
            $plant->setParticular($this);
        }

        return $this;
    }

    public function removePlant(Plant $plant): static
    {
        if ($this->plants->removeElement($plant)) {
            // set the owning side to null (unless already changed)
            if ($plant->getParticular() === $this) {
                $plant->setParticular(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Request>
     */
    public function getRequests(): Collection
    {
        return $this->requests;
    }

    public function addRequest(Request $request): static
    {
        if (!$this->requests->contains($request)) {
            $this->requests->add($request);
            $request->setParticular($this);
        }

        return $this;
    }

    public function removeRequest(Request $request): static
    {
        if ($this->requests->removeElement($request)) {
            // set the owning side to null (unless already changed)
            if ($request->getParticular() === $this) {
                $request->setParticular(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Address>
     */
    public function getAddress(): Collection
    {
        return $this->address;
    }

    public function addAddress(Address $address): self
    {
        if (!$this->address->contains($address)) {
            $this->address->add($address);
            $address->setParticular($this);
        }

        return $this;
    }

    public function removeAddress(Address $address): self
    {
        if ($this->address->removeElement($address)) {
            // set the owning side to null (unless already changed)
            if ($address->getParticular() === $this) {
                $address->setParticular(null);
            }
        }

        return $this;
    }

    public function getFullName(): string
    {
        return $this->getFirstName().' '.$this->getLastName();
    }

    public function getPlantCount(): int
    {
        return $this->plants->count();
    }
}

import {initHandleFileSelect} from "../../tools/handleFileSelected";

export function initPlantEditFormPopup() {
  let plantFormPopup = document.getElementById("plant-form-popup");
  let openPlantDetailsLink = document.getElementById("open-plant-details");
  let cancelPlantButton = document.getElementById("cancel-plant-button");

  if (openPlantDetailsLink) {
    openPlantDetailsLink.addEventListener("click", (e) => {
      e.preventDefault();
      if (plantFormPopup) {
        plantFormPopup.style.display = "block";
        setTimeout(() => {
          plantFormPopup.style.opacity = "1";
        }, 10);
      }
    });
  }

  if (cancelPlantButton) {
    cancelPlantButton.addEventListener("click", (e) => {
      e.preventDefault();
      if (plantFormPopup) {
        plantFormPopup.style.opacity = "0";
        setTimeout(() => {
          plantFormPopup.style.display = "none";
        }, 250);
      }
    });
  }
}

export function initPlantAddFormPopup() {
  document.addEventListener("DOMContentLoaded", () => {
    let openPlantFormLink = document.getElementById("open-plant-form");

    openPlantFormLink.addEventListener("click", (e) => {
      e.preventDefault();

      fetch("/plant/new-form", {
        method: "GET",
        headers: {
          "X-Requested-With": "XMLHttpRequest",
        },
      })
        .then((response) => {
          if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
          }
          return response.text();
        })
        .then((formHtml) => {
          let modalDiv = document.createElement("div");
          modalDiv.innerHTML = formHtml;

          // add the modal to the DOM
          document.body.appendChild(modalDiv);

          let plantModal = document.getElementById("plant-modal");
          plantModal.style.display = "block";

          let closeModalButton = document.getElementById("close-plant-modal");
          if (closeModalButton) {
            closeModalButton.addEventListener("click", () => {
              plantModal.style.display = "none";
            });
          }

          let fileInput = document.querySelector("#plant_photos");
          if (fileInput) {
            fileInput.addEventListener("change", initHandleFileSelect);
          }

          let form = plantModal.querySelector("form");

          form.addEventListener("submit", (e) => {
            e.preventDefault();

            let formData = new FormData(form);
            fetch("/plant/new", {
              method: "POST",
              body: formData,
              headers: {
                "X-Requested-With": "XMLHttpRequest",
              },
            })
              .then((response) => response.json())
              .then((data) => {
                if (data.message === "Success!") {
                  plantModal.style.opacity = "0";
                  setTimeout(() => {
                    plantModal.style.display = "none";
                  }, 250);

                  location.reload();
                } else {
                  console.error(data.errors);
                }
              })
              .catch((error) => {
                console.error("Error submitting form:", error);
              });
          });
        })
        .catch((error) => {
          console.error("Error fetching form:", error);
        });
    });
  });
}

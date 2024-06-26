export function initAddressFormPopup() {
  document.addEventListener("DOMContentLoaded", () => {
    let openAddressModal = document.getElementById("open-address-form");

    openAddressModal.addEventListener("click", (e) => {
      e.preventDefault();

      fetch("/address/new-form", {
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

          let addressModal = document.getElementById("addressModal");
          addressModal.style.display = "block";

          let closeModalButton = document.getElementById("closeModalButton");

          if (closeModalButton) {
            closeModalButton.addEventListener("click", () => {
              addressModal.style.display = "none";
            });
          }

          let addressForm = addressModal.querySelector("#address-form");

          addressForm.addEventListener("submit", (e) => {
            e.preventDefault();

            let formData = new FormData(addressForm);

            fetch("/address/new", {
              method: "POST",
              body: formData,
              headers: {
                "X-Requested-With": "XMLHttpRequest",
              },
            })
              .then((response) => response.json())
              .then((data) => {
                if (data) {
                  addressModal.style.opacity = "0";
                  setTimeout(() => {
                    addressModal.style.display = "none";
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
          console.error("Error:", error);
        });
    });
  });
}

export function initAddressDelete() {
  document.addEventListener("DOMContentLoaded", () => {
    const deleteButtons = document.querySelectorAll(".delete-address");

    deleteButtons.forEach((button) => {
      button.addEventListener("click", (e) => {
        e.preventDefault();

        const addressId = e.target.getAttribute("data-address-id");

        fetch(`/address/${addressId}/delete`, {
          method: "POST",
          headers: {
            "X-Requested-With": "XMLHttpRequest",
          },
        })
          .then((response) => {
            if (response.status === 204) {
              return {message: "Address deleted successfully"};
            }
            return response.json();
          })
          .then((data) => {
            if (data.message === "Address deleted successfully") {
              e.target.closest("li").remove();
              location.reload();
            } else {
              console.error("Error deleting address:", data.message);
            }
          })
          .catch((error) => {
            console.error("Error:", error);
          });
      });
    });
  });
}

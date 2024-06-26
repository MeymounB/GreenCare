export function initDeleteAccountPopup() {
  let popup = document.getElementById("delete-account-popup");
  let cancelButton = document.getElementById("cancel-button");
  let confirmButton = document.getElementById("confirm-button");
  let deleteAccountLink = document.getElementById("delete-account-link");

  if (deleteAccountLink) {
    deleteAccountLink.addEventListener("click", (e) => {
      e.preventDefault();
      if (popup) {
        popup.style.display = "block";
        setTimeout(() => {
          popup.style.opacity = "1";
        }, 10);
      }
    });
  }

  function hidePopup() {
    if (popup) {
      popup.style.opacity = "0";
      setTimeout(() => {
        popup.style.display = "none";
      }, 300);
    }
  }

  if (cancelButton) {
    cancelButton.addEventListener("click", hidePopup);
  }

  if (confirmButton) {
    let deleteForm = document.getElementById("delete-form");

    confirmButton.addEventListener("click", function () {
      fetch(deleteForm.action, {
        method: "DELETE",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8",
        },
        body: new URLSearchParams(new FormData(deleteForm)).toString(),
      })
        .then((response) => {
          if (!response.ok) {
            throw response;
          }
          console.log("The element has been deleted");
          window.location.href = "/";
        })
        .catch((error) => {
          error.text().then((errorMessage) => {
            console.log(
              "An error occured while deleting the element: " + errorMessage,
              errorMessage
            );
          });
        });
      hidePopup();
    });
  }
}

const apiBase = "/cliques-lia";
const usersEndpoint = `${apiBase}/users`;

async function updateConsole(url) {
  const outputElement = document.getElementById("json-output");
  const statusElement = document.getElementById("http-status");

  try {
    const response = await fetch(url);
    statusElement.innerText = `HTTP ${response.status} ${response.statusText}`;

    const payload = await response.json();
    outputElement.innerText = JSON.stringify(payload, null, 4);
  } catch (error) {
    statusElement.innerText = "HTTP 500 Connection Error";
    outputElement.innerText = `Network trace extraction failed:\n${error.message}`;
  }

  document.addEventListener("DOMContentLoaded", () => {
    fetchUsers();
  });

  document
    .getElementById("userForm")
    .addEventListener("submit", async function (event) {
      event.preventDefault();

      const userData = {
        username: document.getElementById("username").value,
        user_email: document.getElementById("user_email").value,
        user_birthday: document.getElementById("user_birthday").value,
        user_status: document.getElementById("userWarningStatus").value,
        password: document.getElementById("userPassword").value,
      };

      try {
        const response = await fetch(usersEndpoint, {
          method: "POST",

          header: {
            "Content-Type": "application/json",
          },

          body: JSON.stringify(userData),
        });

        const result = await response.json();

        //error check here or success message

        document.getElementById("userForm").requestFullscreen();

        fetchUsers();
      } catch (error) {
        //error message
      }
    });
}

async function fetchUsers() {
  const response = await fetch(usersEndpoint);
  const users = await response.json();

  displayUsers(users);
}

function displayUsers(users) {
  const tableBody = document.getElementById("userTableBody");

  tableBody.innerHTML = "";

  users.forEach((element) => {
    const row = document.createElement("tr");
    row.innerHTML = `
    <td>${users.id}</td>
    <td>${users.username}</td>
    <td>${users.user_email}</td>
    <td>${users.user_birthday}</td>
    <td>${users.warning_status == 1 ? "Suspicious Activity" : "No warning"}</td>
    `;

    tableBody.appendChild(row);
  });
}

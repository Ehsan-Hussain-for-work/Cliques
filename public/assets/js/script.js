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
}

async function fetchUsers() {
  const response = await fetch(usersEndpoint);
  const payload = await response.json();
  displayUsers(payload.data);
}

function displayUsers(users) {
  const tableBody = document.getElementById("userTableBody");

  tableBody.innerHTML = "";

  users.forEach((user) => {
    const row = document.createElement("tr");
    row.innerHTML = `
    <td class="text-center">${user.id}</td>
    <td class="text-center">${user.username}</td>
    <td class="text-center">${user.user_email}</td>
    <td class="text-center">${user.user_birthday}</td>
    <td class="text-center">${user.warning_status == 1 ? "Suspicious Activity" : "No warning"}</td>
    <td class="text-center">
      <button class="btn btn-info btn-sm onclick="">
      Edit
      </button>
      <button class="btn btn-danger btn-sm onclick="">
      Delete
      </button>
    </td>
    `;

    tableBody.appendChild(row);
  });
}

document.addEventListener("DOMContentLoaded", () => {
  fetchUsers();
});

document
  .getElementById("userForm")
  .addEventListener("submit", async function (event) {
    event.preventDefault();

    const userData = {
      username: document.getElementById("userUsername").value,
      user_email: document.getElementById("userEmail").value,
      user_birthday: document.getElementById("userBirthday").value,
      user_status: document.getElementById("warningStatus").value,
      password: document.getElementById("userPassword").value,
    };

    try {
      const response = await fetch(usersEndpoint, {
        method: "POST",

        headers: {
          "Content-Type": "application/json",
        },

        body: JSON.stringify(userData),
      });

      //error check here or success message

      document.getElementById("userForm").reset();

      fetchUsers();
    } catch (error) {
      //error message
    }
  });

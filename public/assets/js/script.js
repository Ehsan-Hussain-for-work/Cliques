const apiBase = "/cliques-lia";
const usersEndpoint = `${apiBase}/users`;
let currentPage = 1;

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

async function fetchUsers(page = 1) {
  const response = await fetch(`${usersEndpoint}?page=${page}`);
  const payload = await response.json();

  const result = payload.data;

  currentPage = result.metadata.current_page;

  displayUsers(result.data);

  updatePagination(result.metadata, "user", fetchUsers);
}

function updatePagination(metadata, type, functionName) {
  const pagination = document.getElementById(type.concat("Pagination"));

  pagination.innerHTML = "";

  const previousButton = document.createElement("button");

  previousButton.innerText = "Previous";

  if (metadata.current_page <= 1) {
    previousButton.disabled = true;
  } else {
    previousButton.disabled = false;
  }

  previousButton.addEventListener("click", () => {
    functionName(metadata.current_page - 1);
  });

  pagination.appendChild(previousButton);

  for (let num = 1; num <= metadata.total_pages; num++) {
    const pageButton = document.createElement("button");

    pageButton.innerText = num;

    if (num === metadata.current_page) {
      pageButton.disabled = true;
    } else {
      pageButton.disabled = false;
    }

    pageButton.addEventListener("click", () => {
      functionName(num);
    });

    pagination.appendChild(pageButton);
  }

  const nextButton = document.createElement("button");

  nextButton.innerText = "Next";
  nextButton.disabled = metadata.current_page >= metadata.total_pages;

  nextButton.addEventListener("click", () => {
    functionName(metadata.current_page + 1);
  });

  pagination.appendChild(nextButton);
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
    <td class="text-center">${user.warning_status == 1 ? "Warned" : "No warning"}</td>
    <td class="text-center">
      <button class="btn btn-info btn-sm" onclick="">
      Edit
      </button>
      <button class="btn btn-danger btn-sm" onclick="removeUser(${user.id})">
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
      warning_status: document.getElementById("warningStatus").value,
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

      fetchUsers(currentPage);
    } catch {
      //error message
    }
  });

async function removeUser(userId) {
  //confirmation message?
  try {
    const response = await fetch(`${usersEndpoint}/${userId}`, {
      method: "DELETE",
    });

    fetchUsers(currentPage);
  } catch {
    //error message here
  }
}

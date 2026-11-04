const apiBase = "/cliques-lia";
const usersEndpoint = `${apiBase}/users`;
const groupsEndpoint = `${apiBase}/groups`;
let currentUsersPage,
  currentGroupsPage = 1;

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

async function fetchUsers(page = 1) {
  try {
    const response = await fetch(`${usersEndpoint}?page=${page}`);
    const payload = await response.json();

    const usersArray = payload.items;

    const metadata = {
      current_page: payload.page.number,
      total_pages: payload.page.totalPages,
    };
    currentUsersPage = metadata.current_page;

    displayUsers(usersArray);

    updatePagination(metadata, "user", fetchUsers);
  } catch (error) {
    console.error("Failed to fetch users:", error);
    document.getElementById("userTableBody").innerHTML =
      "<tr><td colspan='6' class='text-center'>Failed to load users. Please try again later.</td></tr>";
  }
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
  fetchGroups();
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

      fetchUsers(currentUsersPage);
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

    fetchUsers(currentUsersPage);
  } catch {
    //error message here
  }
}

async function fetchGroups(page = 1) {
  try {
    const response = await fetch(`${groupsEndpoint}?page=${page}`);
    const payload = await response.json();

    const groups = payload.data;
    const metadata = payload.metadata;

    // const result = payload.data;

    currentGroupsPage = metadata.current_page;

    displayGroups(groups);

    updatePagination(metadata, "group", fetchGroups);
  } catch (error) {
    console.error("Failed to fetch groups:", error);
    document.getElementById("groupTableBody").innerHTML =
      "<tr><td colspan='5' class='text-center'>Failed to load groups. Please try again later.</td></tr>";
  }
}

function displayGroups(groups) {
  const tableBody = document.getElementById("groupTableBody");

  tableBody.innerHTML = "";

  groups.forEach((group) => {
    const row = document.createElement("tr");
    row.innerHTML = `
    <td class="text-center">${group.id}</td>
    <td class="text-center">${group.name}</td>
    <td class="text-center">${group.creation_date}</td>
    <td class="text-center">${group.adult_gate == 1 ? "Adult only" : "All ages"}</td>
    <td class="text-center">
      <button class="btn btn-info btn-sm" onclick="">
      Edit
      </button>
      <button class="btn btn-danger btn-sm" onclick="removeGroup(${group.id})">
      Delete
      </button>
    </td>
    `;

    tableBody.appendChild(row);
  });
}

document
  .getElementById("groupForm")
  .addEventListener("submit", async function (event) {
    event.preventDefault();

    const groupData = {
      name: document.getElementById("groupName").value,
      creation_date: document.getElementById("groupCreationDate").value,
      adult_gate: document.getElementById("groupAdultGate").value,
    };

    try {
      const response = await fetch(groupsEndpoint, {
        method: "POST",

        headers: {
          "Content-Type": "application/json",
        },

        body: JSON.stringify(groupData),
      });

      //error check here or success message

      document.getElementById("groupForm").reset();

      fetchGroups(currentGroupsPage);
    } catch {
      //error message
    }
  });

async function removeGroup(groupId) {
  //confirmation message?
  try {
    const response = await fetch(`${groupsEndpoint}/${groupId}`, {
      method: "DELETE",
    });

    fetchGroups(currentGroupsPage);
  } catch {
    //error message here
  }
}

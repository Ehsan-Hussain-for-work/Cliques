const apiBase = "/cliques-lia";
const usersEndpoint = `${apiBase}/users`;
const groupsEndpoint = `${apiBase}/groups`;
const channelsEndpoint = `${apiBase}/channels`;
let currentUsersPage,
  currentGroupsPage,
  currentChannelsPage = 1;

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

document.addEventListener("DOMContentLoaded", () => {
  fetchUsers();
  fetchGroups();
  fetchChannels();
});

async function fetchUsers(page = 1) {
  const response = await fetch(`${usersEndpoint}?page=${page}`);
  const payload = await response.json();

  const result = payload.data;

  currentUsersPage = result.metadata.current_page;

  displayUsers(result.data);

  updatePagination(result.metadata, "user", fetchUsers);
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

async function fetchGroups(page = 1) {
  const response = await fetch(`${groupsEndpoint}?page=${page}`);
  const payload = await response.json();

  const result = payload.data;

  currentGroupsPage = result.metadata.current_page;

  displayGroups(result.data);

  updatePagination(result.metadata, "group", fetchGroups);
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

async function fetchChannels(page = 1) {
  const response = await fetch(`${channelsEndpoint}?page=${page}`);
  const payload = await response.json();

  const result = payload.data;

  currentChannelsPage = result.metadata.current_page;

  displayChannels(result.data);

  updatePagination(result.metadata, "channel", fetchChannels);
}

function displayChannels(channels) {
  const tableBody = document.getElementById("channelTableBody");

  tableBody.innerHTML = "";

  channels.forEach((channel) => {
    const row = document.createElement("tr");
    row.innerHTML = `
    <td class="text-center">${channel.id}</td>
    <td class="text-center">${channel.name}</td>
    <td class="text-center">${channel.group_id}</td>
    <td class="text-center">${channel.adult_gate == 1 ? "Adult only" : "All ages"}</td>
    <td class="text-center">
      <button class="btn btn-info btn-sm" onclick="">
      Edit
      </button>
      <button class="btn btn-danger btn-sm" onclick="removeChannel(${channel.id})">
      Delete
      </button>
    </td>
    `;

    tableBody.appendChild(row);
  });
}

async function removeChannel(channelId) {
  //confirmation message?
  try {
    const response = await fetch(`${channelsEndpoint}/${channelId}`, {
      method: "DELETE",
    });

    fetchChannels(currentChannelsPage);
  } catch {
    //error message here
  }
}

document
  .getElementById("channelForm")
  .addEventListener("submit", async function (event) {
    event.preventDefault();

    const channelData = {
      name: document.getElementById("channelName").value,
      group_id: document.getElementById("channelGroupId").value,
      adult_gate: document.getElementById("channelAdultGate").value,
    };

    try {
      const response = await fetch(channelsEndpoint, {
        method: "POST",

        headers: {
          "Content-Type": "application/json",
        },

        body: JSON.stringify(channelData),
      });

      //error check here or success message

      document.getElementById("channelForm").reset();

      fetchChannels(currentChannelsPage);
    } catch {
      //error message
    }
  });

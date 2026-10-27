const apiBase = "/cliques-lia";

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

function fetchCollection() {
  updateConsole(`${apiBase}/users`);
}

function fetchIndividual() {
  const userId = document.getElementById("user-id").value;
  updateConsole(`${apiBase}/users/${userId}`);
}

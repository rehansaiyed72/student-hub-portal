let events = [];

let currentPage = 1;

let itemsPerPage = 3;


// Fetch JSON data
fetch("../data/events.json")

    .then(function(response) {

        if (!response.ok) {
            throw new Error("Unable to load events");
        }

        return response.json();

    })

    .then(function(data) {

        events = data;

        document.getElementById("eventLoading").innerHTML = "";

        displayEvents();

    })

    .catch(function(error) {

        document.getElementById("eventLoading").innerHTML =
            "Error loading events.";

        console.error(error);

    });


// Display events
function displayEvents() {

    let searchText =
        document.getElementById("eventSearch").value.toLowerCase();

    let category =
        document.getElementById("eventFilter").value;

    let sortBy =
        document.getElementById("eventSort").value;


    // Search
    let filteredEvents = events.filter(function(event) {

        return event.title.toLowerCase().includes(searchText);

    });


    // Filter
    if (category !== "all") {

        filteredEvents = filteredEvents.filter(function(event) {

            return event.category === category;

        });

    }


    // Sort
    if (sortBy === "name") {

        filteredEvents.sort(function(a, b) {

            return a.title.localeCompare(b.title);

        });

    }

    if (sortBy === "date") {

        filteredEvents.sort(function(a, b) {

            return new Date(a.date) - new Date(b.date);

        });

    }


    // Pagination
    let start = (currentPage - 1) * itemsPerPage;

    let end = start + itemsPerPage;

    let pageEvents = filteredEvents.slice(start, end);


    let eventList = document.getElementById("eventList");

    eventList.innerHTML = "";


    if (pageEvents.length === 0) {

        eventList.innerHTML = "<p>No events found.</p>";

        document.getElementById("eventPagination").innerHTML = "";

        return;

    }


    // Render events
    pageEvents.forEach(function(event) {

        eventList.innerHTML += `
            <section>
                <h3>${event.title}</h3>
                <p><b>Date:</b> ${event.date}</p>
                <p><b>Venue:</b> ${event.venue}</p>
                <p><b>Category:</b> ${event.category}</p>
            </section>
        `;

    });


    createPagination(filteredEvents.length);

}


// Search
document.getElementById("eventSearch").addEventListener("input", function() {

    currentPage = 1;

    displayEvents();

});


// Filter
document.getElementById("eventFilter").addEventListener("change", function() {

    currentPage = 1;

    displayEvents();

});


// Sort
document.getElementById("eventSort").addEventListener("change", function() {

    currentPage = 1;

    displayEvents();

});


// Create pagination buttons
function createPagination(totalItems) {

    let totalPages = Math.ceil(totalItems / itemsPerPage);

    let pagination =
        document.getElementById("eventPagination");

    pagination.innerHTML = "";


    for (let i = 1; i <= totalPages; i++) {

        pagination.innerHTML += `
            <button onclick="changePage(${i})">
                ${i}
            </button>
        `;

    }

}


// Change page
function changePage(page) {

    currentPage = page;

    displayEvents();

}
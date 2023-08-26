"use strict";
$( document ).ready(function() {
  // just some preliminary stuff for demonstration

  if (window.infosys.ticket_id !== undefined) {
    show_ticket(window.infosys.ticket_id);
  } else {
    show_ticket_list();
  }

  function show_ticket(id) {
    $.get(
      'https://infosys-test.fastaval.dk/tickets/ajax',
      {
        id: id
      },
      function(data, status) {
        if (data['status'] == 'success') {
          show_ticket_info(Object.values(data.tickets)[0]);
        } else {
          alert("Fejl under hentning af opgave fra serveren");
          console.log("Ticket list response:", data, "Status:", status);
        }
      }
    )

    function show_ticket_info(ticket) {
      let created = new Date(ticket.created*1000);
      let edited = new Date(ticket.last_edit*1000);

      $('.tickets-wrapper').html(`
        <button onclick="window.location ='/tickets'">Tilbage</button>
        <h2>${ticket.name} ID:${ticket.id}</h2>
        <p>Oprettet af:${ticket.creator}, ${created}</p>
        <p>Udføres af af:${ticket.assignee}</p>
        <p>Sidst opdateret: ${edited} (${ticket.open ? 'Åben' : 'Lukket'})</p>
        <h3>Beskrivelse:</h3>
        <p>${ticket.description}</p>
      `);
    }
  }

  function show_ticket_list() {
    $('.tickets-wrapper').html('<h3>Opgaver til tilmelding og infosys<h3>');

    $.get(
      'https://infosys-test.fastaval.dk/tickets/ajax',
      {},
      function(data, status) {
        if (data['status'] == 'success') {
          create_tickets_table(data.tickets);
        } else {
          alert("Fejl under hentning af opgaver fra serveren");
          console.log("Ticket list response:", data, "Status:", status);
        }
      }
    )
  }

  function create_tickets_table(tickets) {
    let wrapper = $('.tickets-wrapper');

    let table = $('<table id="ticket-list"><thead></thead><tbody></tbody><table>');
    wrapper.append(table);

    let headers = {
      id: 'ID',
      category: 'Kategori',
      name: 'Opgave',
      priority: 'Prioritet',
      creator: 'Oprettet af',
      assignee: 'Udføres af',
      status: 'Status'
    }
    
    let header = table.find('thead');
    for(const column in headers) {
      header.append(`<th>${headers[column]}</th>`);
    }

    let body = table.find('tbody');
    for (const id in tickets) {
      let row = $(`<tr onclick="window.location='/tickets/show/${id}'"></tr>`);
      for(const column in headers) {
        row.append(`<td>${tickets[id][column]}</td>`);
      }
      body.append(row);
    }
  }



  //buttons for testing
  let create_button = $('<button>Create Ticket</button>');
  $('.tickets-wrapper').after(create_button);
  create_button.on('click', function() {
    $.post(
      'https://infosys-test.fastaval.dk/tickets/ajax',
      {
        'name':'Test Opgave',
        'description': 'Opgave oprettet via test interface',
      },
      function(data, status) {
        console.log("Create ticket response:", data, "Status:", status);
      }
    )
  })

  let list_button = $('<button>Ticket List</button>');
  $('.tickets-wrapper').after(list_button);
  list_button.on('click', function() {
    $.get(
      'https://infosys-test.fastaval.dk/tickets/ajax',
      {},
      function(data, status) {
        console.log("Ticket list response:", data, "Status:", status);
      }
    )
  })

  if (window.infosys.ticket_id !== undefined) {
    let update_button = $('<button>Update Ticket</button>');
    $('.tickets-wrapper').after(update_button);
    update_button.on('click', function() {
      $.post(
        'https://infosys-test.fastaval.dk/tickets/ajax',
        {
          'id':window.infosys.ticket_id,
          'name':'Rettet Opgave',
          'description': 'Denne opgave er rettet med testknap',
        },
        function(data, status) {
          console.log("Update ticket response:", data, "Status:", status);
          location.reload();
        }
      )
    })
  }


});
"use strict";
$( function() {
  let infosys = window.infosys;
  let lang = 'da';

  // just some preliminary stuff for demonstration
  let saved_data = {};
  let waiting = {
    next: 1,
    functions: [],
    name_list: []
  };

  $.get(
    '/translations/ajax/tickets.*',
    {
      lang
    },
    function(data, status) {
      if (data['status'] == 'success') {
        resolve('translations', data.translations.tickets);
      } else {
        alert("Fejl under hentning af oversættelser fra serveren");
        console.log("Ticket list response:", data, "Status:", status);
      }
    }
  );

  $.get(
    '/admin/ajax/users/*',
    {},
    function(data, status) {
      if (data['status'] == 'success') {
        resolve('users', data.users);
      } else {
        alert("Fejl under hentning af brugere fra serveren");
        console.log("users list response:", data, "Status:", status);
      }
    }
  );

  function await(names, func) {
    if (!Array.isArray(names)) {
      names = [names];
    }

    let wait_names = [];
    names.forEach(function(name) {
      if (saved_data[name] === undefined) {
        wait_names.push(name);
      }
    });

    if (wait_names.length == 0) {
      func();
      return;
    }

    waiting.functions[waiting.next] = func;
    waiting.name_list[waiting.next] = wait_names;
    waiting.next++;
  }

  function resolve(name, data) {
    saved_data[name] = data;

    waiting.name_list.forEach(function(names, index) {
      let name_index = names.indexOf(name);
      if (name_index != -1) {
        waiting.name_list[index].splice(name_index, 1);
      }

      if (waiting.name_list[index].length == 0) {
        waiting.functions[index]();
        delete waiting.name_list[index];
        delete waiting.functions[index];
      }
    });
  }

  // Check if we have a ticket id set aka are we looking at a specific ticket
  if (infosys.ticket_id !== undefined) {
    show_ticket(infosys.ticket_id);
  } else {
    show_ticket_list();
  }

  // Show specific ticket
  function show_ticket(id) {
    window.history.pushState({id},"", `/tickets/show/${id}`);
    infosys.ticket_id = id;
    $.get(
      '/tickets/ajax',
      {
        id: id
      },
      function(data, status) {
        if (data['status'] == 'success') {
          await( ['translations', 'users'] , function() {
            show_ticket_info(Object.values(data.tickets)[0]);
          })
        } else {
          alert("Fejl under hentning af opgave fra serveren");
          console.log("Ticket list response:", data, "Status:", status);
        }
      }
    )

    // Show base info on the ticket
    function show_ticket_info(ticket) {
      let created = new Date(ticket.created*1000);
      let edited = new Date(ticket.last_edit*1000);
      let ts = saved_data.translations;
      let us = saved_data.users;

      let priority = ts.priority[ticket.priority][lang];
      let open = ticket.open ? ts.open[lang] : ts.closed[lang];

      let wrapper = $('.tickets-wrapper');
      wrapper.html(`
        <button onclick="window.infosys.tickets.show_ticket_list()">Tilbage</button>
        <h2>${ticket.name} ID:${ticket.id} (${open})</h2>
        <p>Oprettet af:${us[ticket.creator]?.name || 'Ukendt'}, ${created}</p>
        <p>Sidst opdateret: ${edited} </p>
        <p>Udføres af af:${us[ticket.assignee]?.name} || 'Ukendt'</p>
        <p>Prioritet: ${priority}</p>
        <h3>Beskrivelse:</h3>
        <p>${ticket.description}</p>
      `);

      $.get(
        `/tickets/${ticket.id}/messages`,
        function(data, status) {
          if (data['status'] == 'success') {
            list_comments(data.messages);
          } else {
            alert("Fejl under hentning af kommentarer");
            console.log("Message list response:", data, "Status:", status);
          }
        }
      ).complete(function() {
        set_buttons();
      })

      function list_comments(messages) {
        if (messages.length > 0) {
          wrapper.append('<h3>Kommentarer:</h3>');
        }

        for (const message of messages) {
          let edited = message.last_edit === null ? "" :
            `<p class="comment-edited">Edited on ${new Date(message.last_edit*1000)}</p>`;

          let div = $(`<div class="comment-div" message-id="${message.id}"></div>`);
          div.html(`
            <p class="comment-header">Posted by ${message.user} on ${new Date(message.posted*1000)}</p>
            ${edited}
            <p class="comment-body">${message.message}</p>`
          );
          wrapper.append(div);
        }
      }
    }
  }

  // Show the list of all tickets
  function show_ticket_list() {
    window.history.pushState({id : null},"", `/tickets`);
    infosys.ticket_id = undefined;

    $('.tickets-wrapper').html('<h3>Opgaver til tilmelding og infosys<h3>');

    $.get(
      '/tickets/ajax',
      {},
      function(data, status) {
        if (data['status'] == 'success') {
          await(['translations', 'users'], function() {
            create_tickets_table(data.tickets);
          })
        } else {
          alert("Fejl under hentning af opgaver fra serveren");
          console.log("Ticket list response:", data, "Status:", status);
        }
      }
    )

    // Create table of all tickets
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
        let row = $(`<tr onclick="window.infosys.tickets.show_ticket(${id})"></tr>`);
        for(const column in headers) {
          let text = tickets[id][column];

          let ts = saved_data.translations;
          let us = saved_data.users;
          if (column == 'status') {
            let open = tickets[id].open ? 'open' : 'closed';
            text = ts.status[open][text]?.[lang] ?? text;
          } else if (column == 'creator' || column == 'assignee') {
            text = us[text]?.name ?? 'Ukendt';
          } else {
            text = ts[column]?.[text]?.[lang] ?? text;
          }
          
          row.append(`<td>${text}</td>`);
        }
        body.append(row);
      }
    }
    set_buttons();
  }

  //-----------------------------
  // Buttons for testing
  //-----------------------------
  function set_buttons() {
    let buttons_div = $('.buttons-div');
    if (buttons_div.length == 0) {
      buttons_div = $('<div class="buttons-div"></div>');
      $('.tickets-wrapper').after(buttons_div);
    }
    buttons_div.html('');
    
    if (infosys.ticket_id === undefined){
      // Buttons only for when we are on the main ticket page

      // Create ticket
      let create_button = $('<button>Create Ticket</button>');
      buttons_div.append(create_button);
      create_button.on('click', function() {
        $.post(
          '/tickets/ajax',
          {
            'name':'Test Opgave',
            'description': 'Opgave oprettet via test interface',
          },
          function(data, status) {
            console.log("Create ticket response:", data, "Status:", status);
            location.reload();
          }
        )
      })
    } else {
      // Buttons only for when we have a current ticket
      
      // Do a test update of a ticket
      let update_button = $('<button>Update Ticket</button>');
      buttons_div.append(update_button);
      update_button.on('click', function() {
        $.post(
          '/tickets/ajax',
          {
            'id':infosys.ticket_id,
            'name':'Rettet Opgave',
            'description': 'Denne opgave er rettet med testknap',
          },
          function(data, status) {
            console.log("Update ticket response:", data, "Status:", status);
            location.reload();
          }
        )
      })

      // Post a message on a ticket
      let post_message_button = $('<button>Post Message</button>');
      buttons_div.append(post_message_button);
      post_message_button.on('click', function() {
        $.post(
          `/tickets/${infosys.ticket_id}/messages`,
          {
            message: 'Message posted via test interface',
          },
          function(data, status) {
            console.log("Post message response:", data, "Status:", status);
            location.reload();
          }
        )
      })

      // Update message text
      $('.comment-div').each(function() {
        let message_div = $(this);

        let update_message_button = $('<button>Update Message</button>');
        message_div.append(update_message_button);
        update_message_button.on('click', function () {
          $.post(
            `/tickets/${infosys.ticket_id}/messages`,
            {
              id: message_div.attr('message-id'),
              message: 'Message updated via test interface',
            },
            function(data, status) {
              console.log("Update message response:", data, "Status:", status);
              location.reload();
            }
          ).fail(function (jqXHR) {
            console.log('Update message failed', jqXHR);
            let response = JSON.parse(jqXHR.responseText);
            let message = "Could not update comment";
            if (response != null) message += "\n" + response.message;
            alert(message);
          })
        });
      })
    }
  }

  infosys.tickets = {
    show_ticket,
    show_ticket_list,
  }

});
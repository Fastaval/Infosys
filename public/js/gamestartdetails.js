jQuery(function () {
    var handleConfirmVoteCreation = function (e) {
            if (!confirm('Der er allerede printet stemmesedler for denne spilstart. Printer du igen vil de eksisterende stemmesedler blive ugyldige. Er du sikker på du vil fortsætte?')) {
                e.preventDefault();

                return false;
            }

            if (!confirm('Du er helt sikker på de tidligere stemmesedler skal gøres ugyldige?')) {
                e.preventDefault();

                return false;
            }
        };
    jQuery('body').on('click', '.prepareVotes.hasVotes', handleConfirmVoteCreation);
});

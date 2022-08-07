$(function () {
    var $form     = $('#edit-gds'),
        $body     = $form.find('tbody'),
        counter   = -1,
        removeRow = function (e) {
            e.preventDefault();
            e.stopPropagation();

            $(this).closest('tr').remove();
        },
        addRow = function (e) {
            var $row   = $(this).closest('tr'),
                valid  = true,
                $clone;

            e.preventDefault();
            e.stopPropagation();

            $row.find('input').each(function () {
                if (this.value === '') {
                    valid = false;
                }
            });

            if (!valid) {
                return;
            }

            $clone = $row.clone();

            $row.find('input').val('');

            $clone.find('input').each(function () {
                var $self = $(this);

                $self.attr('name', $self.attr('name').replace(/\[0\]/, '[' + counter + ']'));
            });

            $clone.find('button').toggleClass('add remove').text('Fjern');

            $body.append($clone);
            counter--;
        };

    

    $form.on('click.remove-row', 'button.remove', removeRow)
        .on('click.add-row', 'button.add', addRow);
});

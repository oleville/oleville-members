// This code helps set up the event-creation page
jQuery(document).ready(function($) {

    // Set up the date and timepickers
    function updateDateTime() {
        $('.time').timepicker();
    }
    updateDateTime();

    // Change event for the repeat checkbox
    $('#repeat').change(function() {
        if($(this).is(":checked")) {
            $('#repeat_datetimes').show();
        }
        else {
            $('#repeat_datetimes').hide();
        }
    });
    $('#repeat').trigger('change');

    // Events when a new datetime is added to/removed from the repeating datetimes
    var i = 1;
    $('#add_repeat_member').click(function() {
        var datetime = '<div class="datetime"><input type="text" class="time" id="st' + i + '" name="st' + i + '" placeholder="7:00pm" value=""/><span>to</span><input type="text" class="time" id="et' + i + '" name="et' + i + '" placeholder="8:00pm" value=""/>';
        datetime += '<select name="dow' + i + '" id="dow' + i + '">'; 
        datetime += '<option value="sunday">Sunday</option>';
        datetime += '<option value="monday">Monday</option>';
        datetime += '<option value="tuesday">Tuesday</option>';
        datetime += '<option value="wednesday">Wednesday</option>';
        datetime += '<option value="thursday">Thursday</option>';
        datetime += '<option value="friday">Friday</option>';
        datetime += '<option value="saturday">Saturday</option>';

        datetime += '</select><input type="button" class="remove" value="X"></div>';

        $('#max_repeats').val(i);

        i++;
        $(this).before(datetime);
        updateDateTime();
        $('.remove').click(function() {
            $(this).parent(".datetime").remove();
        });
    });
    $('.remove').click(function() {
        $(this).parent(".datetime").remove();
    });

    // Load in the repeated times for the office hours from the member, located in repeat.events

    var repeat_events = JSON.parse(repeat_member.members);

    if(repeat_events.length > 0) {

        // Set the checkbox as checked
        $('#repeat').prop("checked", !$('#repeat').prop("checked"));
        $('#repeat').trigger('change');

        // Populate the repeated date fields
        repeat_events.forEach(function(val, ind) {
            $('#st' + ind).val(val[0]);
            $('#et' + ind).val(val[1]);
            $('#dow' + ind).val(val[2]);
            $('#add_repeat_member').trigger('click');
        });
    }
});
// This code helps set up the event-creation page
jQuery(document).ready(function($) {

    // Set up the date and timepickers
    function updateDateTime() {
        $('.time').timepicker();
        $('.date').datepicker({dateFormat: "yy-mm-dd"});
    }
    updateDateTime();

    $("#start_date").change(function(){
        if(!$("#end_date").val()){
            $("#end_date").val($("#start_date").val());
        }
    })

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
    $('#add_repeat').click(function() {
        var datetime =
            '<div class="datetime"><input type="text" id="sd' + i + '" name="sd' + i + '" class="date" value=""/><input type="text" id="st' + i + '" name="st' + i + '" class="time" value=""/><span>to</span><input type="text" id="et' + i + '" name="et' + i + '" class="time" value=""/><input type="text" id="ed' + i + '" name="ed' + i + '" class="date" value=""/><input type="button" class="remove" value="X"></div>';
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

    // Load in the repeated dates from the event, located in repeat.events
    var repeat_events = JSON.parse(repeat.events);
    console.log(repeat_events);

    if(repeat_events.length > 0) {

        // Set the checkbox as checked
        $('#repeat').prop("checked", !$('#repeat').prop("checked"));
        $('#repeat').trigger('change');

        // Populate the repeated date fields
        repeat_events.forEach(function(val, ind) {
            $('#st'+ind).val(val[0]);
            $('#et'+ind).val(val[1]);
            $('#dow'+ind).val(val[2]);
            $('#add_repeat').trigger('click');
        });
    }
});

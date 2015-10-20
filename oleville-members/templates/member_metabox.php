<label for="position">Position:</label>
<br />
<input type="text" id="position" name="position" placeholder="Coordinator" value="<?php echo get_post_meta($post->ID, 'position', TRUE) ?>"/>
<br />
<label for="class">Class Year:</label>
<br />
<input type="text" id="class" name="class" style="width:76px;" placeholder="16" value="<?php echo get_post_meta($post->ID, 'class', TRUE) ?>"/>
<br />
<label for="subcommittee">Subcommittee (Optional):</label>
<br />
<input type="text" id="subcommittee" name="subcommittee"  placeholder="Bylaws" value="<?php echo get_post_meta($post->ID, 'subcommittee', TRUE) ?>"/>
<br />
<label for="major">Major (Optional):</label>
<br />
<input type="text" id="major" name="major"  placeholder="Math" value="<?php echo get_post_meta($post->ID, 'major', TRUE) ?>"/>
<br />
<label for="subcommittee">Contact (Optional):</label>
<br />
<input type="text" id="contact" name="contact"  placeholder="olethelion@stolaf.edu" value="<?php echo get_post_meta($post->ID, 'contact', TRUE) ?>"/>
<br />
<label for="datetime">Got office hours?</label>
<div id="datetime">
    <input type="text" class="time" id="start_time" name="start_time" placeholder="7:00pm" value="<?php echo get_post_meta($post->ID, 'start_time', TRUE) ?>"/>
    <span>to</span>
    <input type="text" class="time" id="end_time" name="end_time" placeholder="8:00pm" value="<?php echo get_post_meta($post->ID, 'end_time', TRUE) ?>"/>
    <select name="day_of_week">
	  <option value="monday" <?php if(get_post_meta($post->ID, 'day_of_week', TRUE) == 'monday') echo 'selected'; ?>>Monday</option>
	  <option value="tuesday" <?php if(get_post_meta($post->ID, 'day_of_week', TRUE) == 'tuesday') echo 'selected'; ?>>Tuesday</option>
	  <option value="wednesday" <?php if(get_post_meta($post->ID, 'day_of_week', TRUE) == 'wednesday') echo 'selected'; ?>>Wednesday</option>
	  <option value="thursday"<?php if(get_post_meta($post->ID, 'day_of_week', TRUE) == 'thursday') echo 'selected'; ?>>Thursday</option>
	  <option value="friday"<?php if(get_post_meta($post->ID, 'day_of_week', TRUE) == 'friday') echo 'selected'; ?>>Friday</option>
	</select>
</div>
<div id="repeat_container">
    <div id="repeat_checkbox">
        <input type="checkbox" id="repeat" name="repeat" value="repeat"/>
        <span>Got more than one time each week? Check if so.</span>
    </div>
    <div id="repeat_datetimes">
        <div class="datetime">
            <input type="text" id="st0" name="st0" class="time" value=""/>
            <span>to</span>
            <input type="text" id="et0" name="et0" class="time" value=""/>
        	<select name="dow0">
			  <option value="monday" <?php if(get_post_meta($post->ID, 'dow0', TRUE) == 'monday') echo 'selected'; ?>>Monday</option>
			  <option value="tuesday" <?php if(get_post_meta($post->ID, 'dow0', TRUE) == 'tuesday') echo 'selected'; ?>>Tuesday</option>
			  <option value="wednesday" <?php if(get_post_meta($post->ID, 'dow0', TRUE) == 'wednesday') echo 'selected'; ?>>Wednesday</option>
			  <option value="thursday"<?php if(get_post_meta($post->ID, 'dow0', TRUE) == 'thursday') echo 'selected'; ?>>Thursday</option>
			  <option value="friday"<?php if(get_post_meta($post->ID, 'dow0', TRUE) == 'friday') echo 'selected'; ?>>Friday</option>
			</select>
            <input type="button" class="remove" value="X">
        </div>
        <input type="button" id="add_repeat" value="Add" />
    </div>
    <input type="hidden" id="max_repeats" name="max_repeats" value="0" />
</div>
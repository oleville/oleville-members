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
<label for="hometown">Hometown</label>
<br />
<input type="text" id="hometown" name="hometown"  placeholder="Northfield, MN" value="<?php echo get_post_meta($post->ID, 'hometown', TRUE) ?>"/>
<br />
<div id="repeat_container">
    <div id="repeat_checkbox">
        <input type="checkbox" id="repeat" name="repeat" value="repeat"/>
        <span>Got office hours? Check if so.</span>
    </div>
    <div id="repeat_datetimes">
        <div class="datetime">
            <input type="text" class="time" id="st0" name="st0" placeholder="7:00pm" value=""/>
            <span>to</span>
            <input type="text" class="time" id="et0" name="et0" placeholder="8:00pm" value=""/>
            <select name="dow0" id="dow0">
              <option value='sunday' <?php if(get_post_meta($post->ID, 'day_of_week', TRUE) == 'sunday') echo 'selected'; ?>>Sunday</option>  
              <option value="monday" <?php if(get_post_meta($post->ID, 'day_of_week', TRUE) == 'monday') echo 'selected'; ?>>Monday</option>
              <option value="tuesday" <?php if(get_post_meta($post->ID, 'day_of_week', TRUE) == 'tuesday') echo 'selected'; ?>>Tuesday</option>
              <option value="wednesday" <?php if(get_post_meta($post->ID, 'day_of_week', TRUE) == 'wednesday') echo 'selected'; ?>>Wednesday</option>
              <option value="thursday" <?php if(get_post_meta($post->ID, 'day_of_week', TRUE) == 'thursday') echo 'selected'; ?>>Thursday</option>
              <option value="friday" <?php if(get_post_meta($post->ID, 'day_of_week', TRUE) == 'friday') echo 'selected'; ?>>Friday</option>
              <option value='saturday' <?php if(get_post_meta($post->ID, 'day_of_week', TRUE) == 'saturday') echo 'selected'; ?>>Saturday</option>
            </select>
            <input type="button" class="remove" value="X">
        </div>
        <input type="button" id="add_repeat_member" value="Add" />
    </div>
    <input type="hidden" id="max_repeats" name="max_repeats" value="0" />
</div>
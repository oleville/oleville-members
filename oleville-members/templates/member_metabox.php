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
<input type="text" id="subcommittee" name="subcommittee"  placeholder="Special Events" value="<?php echo get_post_meta($post->ID, 'subcommittee', TRUE) ?>"/>
<br />
<label for="major">Major (Optional):</label>
<br />
<input type="text" id="major" name="major"  placeholder="Physics" value="<?php echo get_post_meta($post->ID, 'major', TRUE) ?>"/>
<br />
<label for="subcommittee">Contact (Optional):</label>
<br />
<input type="text" id="contact" name="contact"  placeholder="olethelion@stolaf.edu" value="<?php echo get_post_meta($post->ID, 'contact', TRUE) ?>"/>
<br />
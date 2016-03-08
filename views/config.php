<?php
/**
 * Copyright 2016 Open mHealth
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

/**
 * View for the WordPress admin config section.
 */

namespace OMHSchemaLibrary;
?>

<h2>Configuration Options</h2>

<?php view('options', $data ); ?>
<br/>

<h2>Library Management</h2>

<a  class="button button-primary update-button" href="http://<?php echo remove_qsvar( $data['plugin_full_url'], 'updateLibrary' ) . "&updateLibrary=true"; ?>">Update Library</a>
<span class="update-button-disable-message" style="display:none;"> Please save the changes above before clicking update.</span>

<?php if ( array_key_exists('update_output', $data) && $data['update_output']!='' ): ?>
<div class="update-output">
<h3>Update Result from <?= $data['update_output']['date'] ?>
<?php if( $data['output_old'] ){ ?>
 <em>(previous update)</em>
<?php } else { ?>
 <em>(just now)</em>
<?php } ?></h3>
<pre>
<?php if ( $data['update_output']['git_result'] ){ echo $data['update_output']['git_result']; } ?>

<?php echo $data['update_output']['update_result'] ?>

</pre>

</div>

<?php endif; ?>

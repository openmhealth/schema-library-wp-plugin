<?php
/**
*
* View for the wp admin config section
*
*
**/
namespace OMHSchemaLibrary;
?>

<h1>Schema Library v 1.1</h1>
<hr/>
<h2>Configuration Options</h2>

<?php view('options', $data ); ?>
<br/>

<hr/>
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

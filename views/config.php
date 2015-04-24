<h2>Configuration Options</h2>

<?php view('options', $data ); ?>
<br/>

<h2>Library Management</h2>

<a  class="button button-primary update-button" href="http://<?php echo remove_qsvar( $data['plugin_full_url'], 'updateLibrary' ) . "&updateLibrary=true"; ?>">Update Library</a>

<?php if ( array_key_exists('update_output', $data) ): ?>
<div class="update-output">
<h3>Update Result</h3>
<pre>
<?php if ( $data['update_output']['git_result'] ){ echo $data['update_output']['git_result']; } ?>

<?php echo $data['update_output']['update_result'] ?>

</pre>

</div>

<?php endif; ?>

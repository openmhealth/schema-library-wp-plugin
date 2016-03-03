<?php

$image_column_width = isset($image_column_width)? $image_column_width : 6;
$description_column_width = 12 - $image_column_width;

if ( array_key_exists('button_link',$data) ){
  $button_link = trim($data['button_link_toggle']=="page"? $data['button_link']: $data['button_url']);
  if ( preg_match("@^https?://@", $button_link) == 0 ){
    $button_link = "http://" . $button_link;
  }
  $target = $data['new_window']? "target='window_".rand()."'": "";  
}

$description_heading = array_key_exists('description_heading',$data)? '<h3>' . $data['description_heading'] . '</h3>' : '';

$media = <<<END
<div class="col-sm-{$image_column_width} col-xs-12 image pull-{$data['image_alignment']}">
    <img class="media-object" src="{$data['image']['url']}" alt="{$data['image']['alt']}">
</div>
END
?>


<div class="image-with-description image-<?php echo $data['image_alignment']; ?>">
    <div class="container">
        <div class="row">

          <?php echo $media; ?>

<?php echo <<<END
          <div class="col-sm-{$description_column_width} col-xs-12 text">
              {$description_heading}
              <div class="description">{$data['description']}</div>
END
;
if( array_key_exists('button_link',$data) ){
echo <<<END
              <a href="{$button_link}" role="button" class="btn btn-secondary btn-lg" {$target}>
                  {$data['button_text']}
              </a>
END
;
}
?>
          </div>
        </div>
    </div>
</div>


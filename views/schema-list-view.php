
<div ng-controller="SchemaLibraryView">

    <div class="schema-library">
      <div class="search-panel">
        <input type="text" ng-change="search(searchTerm)" ng-model="searchTerm" ng-model-options="{debounce: 500}"  placeholder="Search for..."/>
        <a ng-click="search(searchTerm)" class="btn btn-default btn-small">search</a>
        <a ng-click="clearSearch()" class="btn btn-default btn-small">clear</a>
      </div>
      
      <div class="loading-search" ng-show="loadingSearch">
          <h3>Loading search results...</h3>
          <div class="text-center">
              <img src="<?= esc_url(bloginfo('template_directory')); ?>/css/images/spinner_small1.gif">
          </div>
      </div>

      <div class="no-results" ng-show="noResults">
          <h3>No schemas matched your terms...</h3>
      </div>

      <ul class="list-group schema-list">
      <?php foreach ( $schema_data as $data ) {
        $multi = $data['count'] > 1;
        $link_type = $multi? 'schema-type' : 'schema';
        ?>
        <li class="list-group-item id-<?=$data['slug']?>">
          <a href="<?php echo $data['url'] ?>">
            <h3>
              <?php echo $data['name'] ?>
              <?php if ( $multi ) {
                echo '<span class="item-count">' . $data['count'] . '</span>';
              } ?>
            </h3>

            <?php if (false) { ?>
            <div class="row">
              <?php if ( array_key_exists( 'deprecated', $data ) && $data['deprecated'] ) {?>
              <div class="col-xs-4 meta-data">
                  <span class="deprecated-label"><span class="glyphicon glyphicon-ban-circle meta-icon"></span>Deprecated</span>
              </div>
              <?php } ?>
            </div>
            <?php } ?>

          </a> 
        </li>
      <?php } ?>
      </ul>
    </div>

</div>

<?php
/**
*
* View for the bread crumbs that help navigate from the schema view
* Includes the dropdown that allows users to select the visible version 
*
*
**/
?>
<!-- schema header -->
<header class="navbar navbar-default navbar-static schema-library-nav" role="banner">
  <div class="navbar-header">
    <div class="schema-nav clearfix">


        <div class="schema-breadcrumb pull-left">
            <?php $terms = wp_get_post_terms( get_the_ID(), 'schema_type', array("fields" => "all") ); ?>
            <a href="<?php echo rtrim( get_site_url(), '/wp') ?>/schemas">Schema Library</a>
            <?php if ( $terms ) { $term = $terms[0]; ?><span class="glyphicon glyphicon-menu-right" aria-hidden="true"></span><a class="schema-type-link" href="<?php echo rtrim( get_site_url(), '/wp') ?>/documentation/#/schema-docs/schema-library/schema-types/<?php echo $term->slug; ?>"><?php echo $term->name; ?></a> <?php } ?>
            <?php if ( is_tax() ) { $term = $wp_query->get_queried_object(); ?><span class="glyphicon glyphicon-menu-right" aria-hidden="true"></span><?php echo $term->name; } ?>
            <span class="schema-name"><?php if ( get_the_ID() ) { ?><span class="glyphicon glyphicon-menu-right" aria-hidden="true"></span>{{schema.title.rendered}} <?php } ?></span>
        </div>
        <?php if ( get_the_ID() ) { ?>
        <div class="schema-version-selector pull-right" ng-show="visibleVersions.length>0">
            <div uib-dropdown class="btn-group" is-open="versionButtonStatus.isopen">
              <button uib-dropdown-toggle type="button" class="btn btn-default btn-small dropdown-toggle" ng-disabled="disabled">
                <span class="dropdown-title">
                  {{selectedVersion.version}}
                  <span ng-if="hasVersionWildcard( selectedVersion.version )">({{getVersionWildcard( selectedVersion.version )}})</span>
                </span>
                <span class="caret"></span>
              </button>
              <ul class="dropdown-menu dropdown-menu-right" role="menu">
                <li ng-repeat="schemaData in visibleVersions" ng-click="changeVersion(schemaData)">
                  <span>
                    {{schemaData.version}}
                    <span ng-if="hasVersionWildcard( schemaData.version )">({{getVersionWildcard( schemaData.version )}})</span>
                  </span>
                </li>
              </ul>
            </div>

        </div>
        <?php } ?>



    </div>
  </div>
</header>

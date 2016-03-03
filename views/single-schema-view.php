
<?php
/**
*
* View for a single schema
*
* Uses built-in wordpress template variables wherever
* content is not interactive and angular template variables
* otherwise. This makes site easier to crawl without javascript.
*
**/

?>

<div ng-controller="SchemaView" schema-post-id="<?php the_ID(); ?>" class="schema-view">

<?php include( "modules/schema-lib-breadcrumb-header.php") ?>

<article <?php post_class(); ?>>
    

    <h1><?=get_the_title()?></h1>

    <?php if ( $deprecation ) { ?>
    <div class="alert-box">
        <span class="glyphicon glyphicon-exclamation-sign"></span> <span class="date"><?= $deprecation_date  ?></span> &mdash; <?= $deprecation['reason'] ?><br/>
        <span class="link-label">Superseded by</span>: <a href="<?= $supersededBy_link ?>"><?= $deprecation['supersededBy'] ?></a>
    </div>
    <?php } ?>

    <header>
        <div class="row">
            <?php
            $author = get_field('author');
            if ( $author ){ ?>
            <div class="col-xs-4 meta-data schema-author">
                <a href="<?=get_field('author_url')?>" title="Author of this schema"><?=get_field('author')?></a>
            </div>
            <?php } ?>
            <div class="col-xs-8 meta-data schema-id"><?=$schema_id?>:{{selectedVersion.version}} <span ng-hide="!selectedVersion.version">(<a href="<?=$schema_url?>/<?=$schema_namespace?>/<?=$schema_file_base?>-{{selectedVersion.version}}.json" title="Reference this URL in your schemas" target="newWin">$ref</a>)</span></div>
        </div>
        <div class="row">
            <div ng-if="selectedVersion.released" class="col-xs-4 meta-data schema-date">
                {{ formatReleaseDate( selectedVersion.released ) }}
            </div>

            <?php if ( get_field('clime_approval') ){ ?>
            <div class="col-xs-8 meta-data schema-clime-approval">
                    <a href="<?=site_url()?>/clinical-measure-working-group-clime/">Clinical Measure Group Approved</a>
            </div>
            <?php } ?>
        </div>
    </header>

    <section class="schema-description">
        <?=the_content()?>
    </section>

    <div class="loading-schema" ng-if="!selectedVersion">
        <h3>{{ loadingMessage }}</h3>
        <div class="text-center" ng-show="visibleVersions.length>0">
            <img src="<?= esc_url(bloginfo('template_directory')); ?>/css/images/spinner_small1.gif">
        </div>
    </div>
    


    <section class="schema-code" ng-if="selectedVersion">
        <a class="scroll-link pull-right" ng-click="scrollTo('sample-data')">Jump to sample data</a>
        <h3>JSON Schema</h3>
        
        <div class="schema-json" formatted-code="selectedVersion.schema_json"></div>

    </section>



    <section class="sample-data" ng-if="selectedSampleData">

        <div class="row heading">

            <div class="col-xs-6">
                <h3 id="sample-data">Sample Data</h3>
            </div>
            <div class="col-xs-6">
                <div class="sample-data-selector pull-right">
                    <a ng-click="cycleSampleData(-1)" class="menu-cycle"><</a>
                    <div uib-dropdown class="btn-group" is-open="sampleDataButtonStatus.isopen">
                      <button uib-dropdown-toggle type="button" class="btn btn-default btn-small dropdown-toggle" ng-disabled="disabled">
                        <span class="dropdown-title">{{$parent.selectedSampleData.title}}</span><span class="caret"></span>
                      </button>
                      <ul uib-dropdown-menu class="dropdown-menu dropdown-menu-right" role="menu">
                        <li ng-repeat="sampleData in $parent.visibleSampleData" ng-click="$parent.$parent.selectedSampleData=sampleData" role="menuitem"><span>{{sampleData.title}}</span></li>
                      </ul>
                    </div>
                    <a ng-click="cycleSampleData(1)" class="menu-cycle">></a>
                </div>
            </div>

        </div>

        <!--  ng-disabled="(sampleData.version!==selectedVersion.version)"  -->
        <div class="sample-data-json" formatted-code="$parent.selectedSampleData.sample_data_json"></div>
    </section>

    
    <?php  $info = get_field('related_information'); if ( $info ){ ?>
    <section class="schema-related-information" >
       <h3>Related Information</h3>
       <?=$info?>
    </section>
    <?php } ?>

    <?php  $contributors = get_field('contributors'); if ( $contributors ){ ?>
    <section class="contributors">
        <h3>Contributors</h3>
        <div class="clearfix">
        <?php foreach( $contributors as $contributor ) { ?>
            <div class="contributor">
                <a href="<?=$contributor['url']?>" target="newWin<?=rand()?>"><img src="<?=$contributor['image_url']?>" alt="<?=$contributor['name']?>"></a>
            </div>
        <?php } ?>
        </div>
    </section>
    <?php } ?>

    <?php  $datasources = get_field('datasources'); if ( $datasources ){ ?>
    <section class="datasources">
        <h3>Data Provider APIs that use this Schema</h3>
        <div><?=$datasources?></div>
    </section>
    <?php } ?>

</article>
</div>


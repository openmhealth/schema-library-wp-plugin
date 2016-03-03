#Schema Library Wordpress Plugin

##What it does

The Open mHealth Schema Library Wordpress plugin generates online documentation for the JSON schemas and sample data contained in a Git repository.

When updated through the Wordpress admin panel, it pulls in each schema, including all versions and sample data, creates a custom post for it in wordpress, and allows the maintainer to enter additional descriptions and metadata.

With each subsequent update, any new schemas found in the repository are added to the library in Wordpress, while existing schemas descriptions and metadata are preserved.


##How to install and test the standalone library

*By hand, in this order:*

1. Go to [wordpress.org](http://www.wordpress.org), get WP, install it
2. Install the [WP REST API v2](http://v2.wp-api.org/) plugin
3. Install the [Advanced Custom Fields Pro](http://www.advancedcustomfields.com/pro/) plugin
4. Go to plugin on GitHub, go to latest release, download [zipfile](https://github.com/openmhealth/schema-library-wp-plugin/archive/master.zip)
5. Install plugin in WP
6. Go to [theme](https://github.com/openmhealth/schema-library-wp-theme) on GitHub, go to latest release, download [zipfile](https://github.com/openmhealth/schema-library-wp-theme/archive/master.zip)
7. Install theme on WP
8. Follow configuration instructions

*Or, using Docker:*

(coming soon)

##Configuration
1. Click the “Schema Library” tab near the bottom of the admin panel’s left navigation
2. Enter the appropriate settings for your organization
3. Click “Save Changes”
4. Click “Update Schemas” at the bottom of the configuration panel
5. Click the “Schemas” tab near the top of the admin panel’s left navigation
6. Edit each schema
7. Enter relevant meta-data
8. Make schema versions visible, as appropriate
9. Click “Publish” in the upper right corner of the editing page

##How to integrate the Schema Library into your project

*If you want to have a dedicated website:*
* Install the website

*If you want a dedicated page on your existing non-WordPress site:*
* Install the website
* Style the wordpress theme to match your site
* Link to the ‘/schemas’ page

*If you have a wordpress website, and you would like to include the library in a page that doesn’t already use angular, copy the following files from the theme repository to your theme directory:*
* single-schema.php
* archive-schema.php
* template-parts/content-single-schema.php
* omh-schema-library-functions.php
* js/omh-schema-library-functions.js
* js/omh-documentation-utilities.js
* css/omh-schema-library-style.css
* css/images

Then, add `include(omh-schema-library-functions.php);` to the end of your theme’s functions.php file.

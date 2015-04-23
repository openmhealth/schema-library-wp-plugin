<div class="options">

  <form method="get" action="http://<?php echo strtok( $data['plugin_full_url'], '?'); ?>">

    <h3>Git Settings</h3>
    <table class="form-table">

      <tr>
        <th scope="row">
          Git sync
        </th>
        <td>
          <input type="hidden" name="git_enabled" value="0" />
          <label for="git_enabled">
            <input type="checkbox" id="git_enabled" name="git_enabled" value="1" <?php echo $data['options']['git_enabled']? 'checked' : ''; ?>>
            enabled
          </label>
        </td>
      </tr>

      <tr>
        <th scope="row">
          <label for="git_repository">Github repository</label><br/>
        </th>
        <td>
          <input type="text" class="regular-text" id="git_repository" name="git_repository" value="<?php echo $data['options']['git_repository']; ?>">
          <p class="description">
            Enter the full github "clone url"<br/>
            e.g. https://github.com/openmhealth/schemas.git
          </p>
        </td>
      </tr>

      <tr>
        <th scope="row">
          <label for="git_branch">Git branch</label><br/>
        </th>
        <td>
          <input type="text" class="regular-text" id="git_branch" name="git_branch" value="<?php echo $data['options']['git_branch']; ?>">
          <p class="description">
            Enter the name of the branch to use<br/>
            If none is entered, the master branch will be used
          </p>
        </td>
      </tr>

    </table>

    <h3>Library Settings</h3>
    <table class="form-table">

      <tr>
        <th scope="row">
          <label for="base_dir">Base directory</label><br/>
        </th>
        <td>
          /<input type="text" class="regular-text" id="base_dir" name="base_dir" value="<?php echo $data['options']['base_dir']; ?>">
          <p class="description">
            Enter the directory inside the repository where the schemas are stored<br/>
            Leave off any leading or trailing slashes, e.g. "schema"
          </p>
        </td>
      </tr>

      <tr>
        <th scope="row">
          <label for="sample_data_dir">Sample data directory</label><br/>
        </th>
        <td>
          /<input type="text" class="regular-text" id="sample_data_dir" name="sample_data_dir" value="<?php echo $data['options']['sample_data_dir']; ?>">
          <p class="description">
            Enter the directory inside the repository where the sample data is stored<br/>
            Leave off any leading or trailing slashes, e.g. "sampleData"
          </p>
        </td>
      </tr>

      <tr>
        <th scope="row">
          <label for="schema_host_url">Hosting url</label><br/>
        </th>
        <td>
          <input type="text" class="regular-text" id="schema_host_url" name="schema_host_url" value="<?php echo $data['options']['schema_host_url']; ?>">
          <p class="description">
            Enter the full url where your organization hosts its schemas<br/>
            e.g. <a href="http://www.openmhealth.org/schema/omh/clinical/body-weight-1.0.json" target="newWin">http://www.openmhealth.org/schema/omh/clinical/body-weight-1.0.json</a>
            is hosted at <a href="http://www.openmhealth.org/schema/" target="newWin">http://www.openmhealth.org/schema/</a>
          </p>
        </td>
      </tr>

    </table>

    <input type="hidden" name="saveOptions" value="true" />
    <input type="hidden" name="page" value="<?php echo $data['page_name']; ?>" />

    <p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes"></p>

  </form>

</div>

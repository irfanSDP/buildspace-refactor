<?php use_stylesheets_for_form($form) ?>
<?php use_javascripts_for_form($form) ?>

<form action="<?php echo url_for('scheduleOfrate/'.($form->getObject()->isNew() ? 'create' : 'update').(!$form->getObject()->isNew() ? '?id='.$form->getObject()->getId() : '')) ?>" method="post" <?php $form->isMultipart() and print 'enctype="multipart/form-data" ' ?>>
<?php if (!$form->getObject()->isNew()): ?>
<input type="hidden" name="sf_method" value="put" />
<?php endif; ?>
  <table>
    <tfoot>
      <tr>
        <td colspan="2">
          <?php echo $form->renderHiddenFields(false) ?>
          &nbsp;<a href="<?php echo url_for('scheduleOfrate/index') ?>">Back to list</a>
          <?php if (!$form->getObject()->isNew()): ?>
            &nbsp;<?php echo link_to('Delete', 'scheduleOfrate/delete?id='.$form->getObject()->getId(), array('method' => 'delete', 'confirm' => 'Are you sure?')) ?>
          <?php endif; ?>
          <input type="submit" value="Save" />
        </td>
      </tr>
    </tfoot>
    <tbody>
      <?php echo $form->renderGlobalErrors() ?>
      <tr>
        <th><?php echo $form['description']->renderLabel() ?></th>
        <td>
          <?php echo $form['description']->renderError() ?>
          <?php echo $form['description'] ?>
        </td>
      </tr>
      <tr>
        <th><?php echo $form['type']->renderLabel() ?></th>
        <td>
          <?php echo $form['type']->renderError() ?>
          <?php echo $form['type'] ?>
        </td>
      </tr>
      <tr>
        <th><?php echo $form['unit']->renderLabel() ?></th>
        <td>
          <?php echo $form['unit']->renderError() ?>
          <?php echo $form['unit'] ?>
        </td>
      </tr>
      <tr>
        <th><?php echo $form['is_header']->renderLabel() ?></th>
        <td>
          <?php echo $form['is_header']->renderError() ?>
          <?php echo $form['is_header'] ?>
        </td>
      </tr>
      <tr>
        <th><?php echo $form['header_number']->renderLabel() ?></th>
        <td>
          <?php echo $form['header_number']->renderError() ?>
          <?php echo $form['header_number'] ?>
        </td>
      </tr>
      <tr>
        <th><?php echo $form['priority']->renderLabel() ?></th>
        <td>
          <?php echo $form['priority']->renderError() ?>
          <?php echo $form['priority'] ?>
        </td>
      </tr>
      <tr>
        <th><?php echo $form['created_at']->renderLabel() ?></th>
        <td>
          <?php echo $form['created_at']->renderError() ?>
          <?php echo $form['created_at'] ?>
        </td>
      </tr>
      <tr>
        <th><?php echo $form['updated_at']->renderLabel() ?></th>
        <td>
          <?php echo $form['updated_at']->renderError() ?>
          <?php echo $form['updated_at'] ?>
        </td>
      </tr>
      <tr>
        <th><?php echo $form['created_by']->renderLabel() ?></th>
        <td>
          <?php echo $form['created_by']->renderError() ?>
          <?php echo $form['created_by'] ?>
        </td>
      </tr>
      <tr>
        <th><?php echo $form['updated_by']->renderLabel() ?></th>
        <td>
          <?php echo $form['updated_by']->renderError() ?>
          <?php echo $form['updated_by'] ?>
        </td>
      </tr>
      <tr>
        <th><?php echo $form['root_id']->renderLabel() ?></th>
        <td>
          <?php echo $form['root_id']->renderError() ?>
          <?php echo $form['root_id'] ?>
        </td>
      </tr>
      <tr>
        <th><?php echo $form['lft']->renderLabel() ?></th>
        <td>
          <?php echo $form['lft']->renderError() ?>
          <?php echo $form['lft'] ?>
        </td>
      </tr>
      <tr>
        <th><?php echo $form['rgt']->renderLabel() ?></th>
        <td>
          <?php echo $form['rgt']->renderError() ?>
          <?php echo $form['rgt'] ?>
        </td>
      </tr>
      <tr>
        <th><?php echo $form['level']->renderLabel() ?></th>
        <td>
          <?php echo $form['level']->renderError() ?>
          <?php echo $form['level'] ?>
        </td>
      </tr>
    </tbody>
  </table>
</form>

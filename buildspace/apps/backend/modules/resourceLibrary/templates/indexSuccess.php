<h1>Samples List</h1>

<table>
  <thead>
    <tr>
      <th>Id</th>
      <th>First name</th>
      <th>Last name</th>
      <th>Email</th>
      <th>Country</th>
      <th>Attributes</th>
      <th>Created at</th>
      <th>Updated at</th>
      <th>Deleted at</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($samples as $sample): ?>
    <tr>
      <td><a href="<?php echo url_for('resourceLibrary/edit?id='.$sample->getId()) ?>"><?php echo $sample->getId() ?></a></td>
      <td><?php echo $sample->getFirstName() ?></td>
      <td><?php echo $sample->getLastName() ?></td>
      <td><?php echo $sample->getEmail() ?></td>
      <td><?php echo $sample->getCountry() ?></td>
      <td><?php echo $sample->getAttributes() ?></td>
      <td><?php echo $sample->getCreatedAt() ?></td>
      <td><?php echo $sample->getUpdatedAt() ?></td>
      <td><?php echo $sample->getDeletedAt() ?></td>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table>

  <a href="<?php echo url_for('resourceLibrary/new') ?>">New</a>

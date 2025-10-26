<h1>Schedule of rate items List</h1>

<table>
  <thead>
    <tr>
      <th>Id</th>
      <th>Description</th>
      <th>Type</th>
      <th>Unit</th>
      <th>Is header</th>
      <th>Header number</th>
      <th>Priority</th>
      <th>Created at</th>
      <th>Updated at</th>
      <th>Created by</th>
      <th>Updated by</th>
      <th>Root</th>
      <th>Lft</th>
      <th>Rgt</th>
      <th>Level</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($schedule_of_rate_items as $schedule_of_rate_item): ?>
    <tr>
      <td><a href="<?php echo url_for('scheduleOfrate/edit?id='.$schedule_of_rate_item->getId()) ?>"><?php echo $schedule_of_rate_item->getId() ?></a></td>
      <td><?php echo $schedule_of_rate_item->getDescription() ?></td>
      <td><?php echo $schedule_of_rate_item->getType() ?></td>
      <td><?php echo $schedule_of_rate_item->getUnit() ?></td>
      <td><?php echo $schedule_of_rate_item->getIsHeader() ?></td>
      <td><?php echo $schedule_of_rate_item->getHeaderNumber() ?></td>
      <td><?php echo $schedule_of_rate_item->getPriority() ?></td>
      <td><?php echo $schedule_of_rate_item->getCreatedAt() ?></td>
      <td><?php echo $schedule_of_rate_item->getUpdatedAt() ?></td>
      <td><?php echo $schedule_of_rate_item->getCreatedBy() ?></td>
      <td><?php echo $schedule_of_rate_item->getUpdatedBy() ?></td>
      <td><?php echo $schedule_of_rate_item->getRootId() ?></td>
      <td><?php echo $schedule_of_rate_item->getLft() ?></td>
      <td><?php echo $schedule_of_rate_item->getRgt() ?></td>
      <td><?php echo $schedule_of_rate_item->getLevel() ?></td>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table>

  <a href="<?php echo url_for('scheduleOfrate/new') ?>">New</a>

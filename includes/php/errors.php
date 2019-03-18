<?php  if (count($errors) > 0){ ?>
<script>$('#errorModal').modal('show');</script>
<div class="text-danger">
	<?php foreach ($errors as $error) : ?>
		<p>&bull; <?php echo $error ?></p>
	<?php endforeach ?>
</div>
<?php } ?>
<?php if(!empty($upozorenja)): ?>
	<div class="alert alert-danger my-alert">
		<?php echo implode('</br>', $upozorenja); ?>
	</div>
<?php 
	endif; 
?>
</div>
</div>
<script src="public/js/script.js"></script>
</body>
</html>
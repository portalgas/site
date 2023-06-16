<?php 
$id = ucfirst($id);
?>

<form action="" class="dropzone" id="my-dropzone<?php echo $id;?>"></form>

<script>
  Dropzone.options.myDropzone<?php echo $id;?> = { // camelized version of the `id`
    url: 'upload',
	parallelUploads: 1,
	uploadMultiple:false,
	maxFiles: 1,
	resizeWidth: 225,
	acceptedFiles: 'image/*',
	paramName: "file", // The name that will be used to transfer the file
    maxFilesize: 3, // MB
	init: function() {
		this.on('addedfile', function(file) {
			if (this.files.length > 1) {
			this.removeFile(this.files[0]);
			}
		});

		<?php 
		if(!empty($img1) && 
		   file_exists(Configure::read('App.root').Configure::read('App.img.upload.article').DS.$organization_id.DS.$img1)) {
		?>			
			let myDropzone = this;
			let mockFile = { name: "Foto articolo", size: 1234 };
			myDropzone.displayExistingFile(mockFile, "<?php echo Configure::read('App.server');?>/images/articles/<?php echo $organization_id;?>/<?php echo $img1;?>");
		<?php 
  		}
		?>
	},
    accept: function(file, done) {
      if (file.name == "justinbieber.jpg") {
        done("dropzone eseguito");
      }
      else { done(); }
    }
  };
</script>
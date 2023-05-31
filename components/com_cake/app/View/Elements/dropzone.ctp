<?php 
$id = ucfirst($id);
?>

<form action="" class="dropzone" id="myDropzone<?php echo $id;?>"></form>

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
	},
    accept: function(file, done) {
      if (file.name == "justinbieber.jpg") {
        done("Naha, you don't.");
      }
      else { done(); }
    }
  };
</script>
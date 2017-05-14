<?php
function upload_data(){
	$target_dir = "uploads/";
	$target_file = $target_dir . basename($_FILES["data"]["name"]);
	$uploadOk = 1;
	$imageFileType = pathinfo($target_file,PATHINFO_EXTENSION);
	// Check if image file is a actual image or fake image
	if(isset($_POST["submit"])) {
		$check = getimagesize($_FILES["data"]["tmp_name"]);
		if($check !== false) {
			echo "File is an image - " . $check["mime"] . ".";
			$uploadOk = 1;
		} else {
			echo "File is not an image.";
			$uploadOk = 0;
		}
	}
	// Check if file already exists
	if (file_exists($target_file)) {
		echo "Sorry, file already exists.";
		$uploadOk = 0;
	}
	// Check file size
	if ($_FILES["data"]["size"] > 500000) {
		echo "Sorry, your file is too large.";
		$uploadOk = 0;
	}
	// Allow certain file formats
	if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
	&& $imageFileType != "gif" ) {
		echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
		$uploadOk = 0;
	}
	// Check if $uploadOk is set to 0 by an error
	if ($uploadOk == 0) {
		echo "Sorry, your file was not uploaded.";
	// if everything is ok, try to upload file
	} else {
		// if (move_uploaded_file($_FILES["data"]["tmp_name"], $target_file)) {
		if (copy($_FILES["data"]["tmp_name"], $target_file)) {
			echo "The file ". basename( $_FILES["data"]["name"]). " has been uploaded. (temp file: " .$_FILES["data"]["tmp_name"] .")";
		} else {
			echo "Sorry, there was an error uploading your file.";
		}
	}
}
if(isset($_POST['upload'])){ // button name
upload_data();
}
?>
  <div class="condition" style="">
                        <table align="center" width="100%" cellspacing="0" cellpadding="3" border="0"  style="background-color: #ccc">
                            <tbody>
                            <form action="" method="post" enctype="multipart/form-data" name="frmUpload" id="frmUpload">
                                <tr>
                                    <td width="20%"><strong>Import Skill</strong></td>                                        
                                    <td>
                                        <input type="file" name="data" value="" id="skill_file">
                                        <input type="submit" name="upload" value="Import" id="" onclick="" >
                                    </td>
                                    <td>
                                        <div id="loadingSkill" class="searching-msg"></div>
                                    </td>
                                    <td>
                                        <span id="skill_msg"></span>
                                    </td>
                                </tr>
                            </form>
                            </tbody>
                        </table>
</div>
<script>
function Upload_data(){

}
</script>
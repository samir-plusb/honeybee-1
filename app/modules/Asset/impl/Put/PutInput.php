<form method="post" action="<?php echo $ro->gen(null); ?>" enctype="multipart/form-data">
    <label for="asset">Select a file</label>
    <input type="file" name="asset" id="asset" />
    <button type="submit">Upload</button>
</form>
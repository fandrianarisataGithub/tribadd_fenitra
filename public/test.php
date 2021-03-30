<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Summernote</title>
  <link href="https://stackpath.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
  <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.js"></script>
    <style>
        .image-upload > input {
            visibility:hidden;
            width:0;
            height:0
        }
    </style>
</head>
<body>
<span style='font-size:100px;'>&#128512;</span>
<p>I will display &#128514;</p>
<p>I will display &#x1F600;</p>
<script>
    $(document).ready(function() {
        $('.summernote').summernote();
    });
</script>
</body>
</html>
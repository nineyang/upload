<?php
// 
	if(!isset($_FILES['fileData'])){
		echo json_encode(['msg' => 'not found' , 'code' => 201]);
		return ;
	}
	$file = $_FILES['fileData'];

	$fileName = __DIR__ . '/tmp/' . $_POST['name'];

	if(is_file($fileName)){
		file_put_contents($fileName , file_get_contents($file['tmp_name']) , FILE_APPEND);
	}else{
		if(move_uploaded_file($file['tmp_name'] , $fileName)){
		echo json_encode([
			'msg' => 'success',
			'code' => 200
		]);
		}else{
			echo json_encode([
				'msg' => 'failed',
				'code' => 201
			]);
		}
	}
	



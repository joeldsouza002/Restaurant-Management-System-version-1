<?php

//tax_action.php

include('rms.php');

$object = new rms();

if(isset($_POST["action"]))
{
	if($_POST["action"] == 'fetch')
	{
		$order_column = array('expense_name', 'expense_amount');

		$output = array();

		$main_query = "
		SELECT * FROM expense_table ";

		$search_query = '';

		if(isset($_POST["search"]["value"]))
		{
			$search_query .= 'WHERE expense_discription LIKE "%'.$_POST["search"]["value"].'%" ';
			$search_query .= 'OR expense_total_amount LIKE "%'.$_POST["search"]["value"].'%" ';
		}

		if(isset($_POST["order"]))
		{
			$order_query = 'ORDER BY '.$order_column[$_POST['order']['0']['column']].' '.$_POST['order']['0']['dir'].' ';
		}
		else
		{
			$order_query = 'ORDER BY expense_id DESC ';
		}

		$limit_query = '';

		if($_POST["length"] != -1)
		{
			$limit_query .= 'LIMIT ' . $_POST['start'] . ', ' . $_POST['length'];
		}

		$object->query = $main_query . $search_query . $order_query;

		$object->execute();

		$filtered_rows = $object->row_count();

		$object->query .= $limit_query;

		$result = $object->get_result();

		$object->query = $main_query;

		$object->execute();

		$total_rows = $object->row_count();

		$data = array();

		foreach($result as $row)
		{
			$sub_array = array();
			$sub_array[] = html_entity_decode($row["expense_discription"]);
			$sub_array[] = $row["expense_amount"] . '%';
			/*$status = '';
			if($row["tax_status"] == 'Enable')
			{
				$status = '<button type="button" name="status_button" class="btn btn-primary btn-sm status_button" data-id="'.$row["tax_id"].'" data-status="'.$row["tax_status"].'">Enable</button>';
			}
			else
			{
				$status = '<button type="button" name="status_button" class="btn btn-danger btn-sm status_button" data-id="'.$row["tax_id"].'" data-status="'.$row["tax_status"].'">Disable</button>';
			}
			$sub_array[] = $status;*/
			$sub_array[] = '
			<div align="center">
			<button type="button" name="edit_button" class="btn btn-warning btn-circle btn-sm edit_button" data-id="'.$row["tax_id"].'"><i class="fas fa-edit"></i></button>
			&nbsp;
			<button type="button" name="delete_button" class="btn btn-danger btn-circle btn-sm delete_button" data-id="'.$row["tax_id"].'"><i class="fas fa-times"></i></button>
			</div>
			';
			$data[] = $sub_array;
		}

		$output = array(
			"draw"    			=> 	intval($_POST["draw"]),
			"recordsTotal"  	=>  $total_rows,
			"recordsFiltered" 	=> 	$filtered_rows,
			"data"    			=> 	$data
		);
			
		echo json_encode($output);

	}

	if($_POST["action"] == 'Add')
	{
		$error = '';

		$success = '';

		$data = array(
			':expense_discription'	=>	$_POST["expense_discription"]
		);

		$object->query = "
		SELECT * FROM expense_table 
		WHERE expense_discription = :expense_discription
		";

		$object->execute($data);

		if($object->row_count() > 0)
		{
			$error = '<div class="alert alert-danger">Expense Already Exists</div>';
		}
		else
		{
			$data = array(
				':expense_discription'	=>	$object->clean_input($_POST["expense_discription"]),
				':expense_total_amount'	=>	$object->clean_input($_POST["expense_total_amount"]),
			);

			$object->query = "
			INSERT INTO expense_table 
			(expense_discription, expense_total_amount) 
			VALUES (:expense_discription, :expense_total_amount)
			";

			$object->execute($data);

			$success = '<div class="alert alert-success">Expense Added</div>';
		}

		$output = array(
			'error'		=>	$error,
			'success'	=>	$success
		);

		echo json_encode($output);

	}

	if($_POST["action"] == 'fetch_single')
	{
		$object->query = "
		SELECT * FROM expense_table 
		WHERE expense_id = '".$_POST["expense_id"]."'
		";

		$result = $object->get_result();

		$data = array();

		foreach($result as $row)
		{
			$data['expense_discription'] = $row['expense_discription'];
			$data['expense_total_amount'] = $row['expense_total_amount'];
		}

		echo json_encode($data);
	}

	if($_POST["action"] == 'Edit')
	{
		$error = '';

		$success = '';

		$data = array(
			':expense_discription'		=>	$_POST["expense_discription"],
			':expense_id'		=>	$_POST['hidden_id']
		);

		$object->query = "
		SELECT * FROM expense_table 
		WHERE expense_discription = :expense_discription 
		AND expense_id != :expense_id
		";

		$object->execute($data);

		if($object->row_count() > 0)
		{
			$error = '<div class="alert alert-danger">Expense Already Exists</div>';
		}
		else
		{

			$data = array(
				':expense_discription'			=>	$object->clean_input($_POST["expense_discription"]),
				':expense_total_amount'	=>	$object->clean_input($_POST["expense_total_amount"]),
			);

			$object->query = "
			UPDATE expense_table 
			SET expense_discription = :expense_discription, 
			expense_total_amount = :expense_total_amount  
			WHERE expense_id = '".$_POST['hidden_id']."'
			";

			$object->execute($data);

			$success = '<div class="alert alert-success">Expense Updated</div>';
		}

		$output = array(
			'error'		=>	$error,
			'success'	=>	$success
		);

		echo json_encode($output);

	}

	/*if($_POST["action"] == 'change_status')
	{
		$data = array(
			':expense_status'		=>	$_POST['next_status']
		);

		$object->query = "
		UPDATE expense_table 
		SET tax_status = :tax_status 
		WHERE tax_id = '".$_POST["id"]."'
		";

		$object->execute($data);

		echo '<div class="alert alert-success">Tax Status change to '.$_POST['next_status'].'</div>';
	}*/

	if($_POST["action"] == 'delete')
	{
		$object->query = "
		DELETE FROM expense_table 
		WHERE expense_id = '".$_POST["id"]."'
		";

		$object->execute();

		echo '<div class="alert alert-success">Expense Deleted</div>';
	}
}

?>
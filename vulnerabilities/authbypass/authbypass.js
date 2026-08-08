function show_save_result (data) {
	if (data.result == 'ok') {
		document.getElementById('save_result').innerText = 'Save Successful';
	} else {
		document.getElementById('save_result').innerText = 'Save Failed';
	}
}

function submit_change(id) {
	var first_name = document.getElementById('first_name_' + id).value;
	var surname = document.getElementById('surname_' + id).value;

	fetch('change_user_details.php', {
		method: 'POST',
		headers: {
			'Accept': 'application/json',
			'Content-Type': 'application/json'
		},
		body: JSON.stringify({ 'id': id, 'first_name': first_name, 'surname': surname })
	}
	)
	.then((response) => response.json())
	.then((data) => show_save_result(data));
}

function populate_form() {
	var xhr= new XMLHttpRequest();
	xhr.open('GET', 'get_user_data.php', true);
	xhr.onreadystatechange= function() {
		if (this.readyState!==4) {
			return;
		}
		if (this.status!==200) {
			return;
		}
		const users = JSON.parse (this.responseText);
		var table_body = document.getElementById('user_table').getElementsByTagName('tbody')[0];
		users.forEach(updateTable);

		/*
		 * Every value below comes from the database and is therefore attacker
		 * controlled. It is inserted with the DOM API (textContent / value
		 * property / addEventListener) instead of innerHTML, so the browser
		 * never parses it as HTML and stored XSS is not possible.
		 */
		function makeInput (id, name, value) {
			var input = document.createElement('input');
			input.type = 'text';
			input.id = id;
			input.name = name;
			input.value = value;          // property assignment, never parsed as HTML
			return input;
		}

		function updateTable (user) {
			var user_id = String(user['user_id']);

			var row = table_body.insertRow(0);

			var cell0 = row.insertCell(-1);
			cell0.appendChild(document.createTextNode(user_id));
			var hidden = document.createElement('input');
			hidden.type = 'hidden';
			hidden.id = 'user_id_' + user_id;
			hidden.name = 'user_id';
			hidden.value = user_id;
			cell0.appendChild(hidden);

			var cell1 = row.insertCell(1);
			cell1.appendChild(makeInput('first_name_' + user_id, 'first_name', user['first_name']));

			var cell2 = row.insertCell(2);
			cell2.appendChild(makeInput('surname_' + user_id, 'surname', user['surname']));

			var cell3 = row.insertCell(3);
			var button = document.createElement('input');
			button.type = 'button';
			button.value = 'Update';
			button.addEventListener('click', function () { submit_change(user_id); });
			cell3.appendChild(button);
		}
	};
	xhr.send();
}

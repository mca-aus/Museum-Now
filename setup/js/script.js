
function setUpFormValidation(fieldsThatCannotBeEmpty) {
	
	$("#submit-form").click(function(e) {
			
		$.each(fieldsThatCannotBeEmpty, function(index, fieldThatCannotBeEmpty) {

			var $fieldThatCannotBeEmpty = $("#" + fieldThatCannotBeEmpty);

			if ($fieldThatCannotBeEmpty.val() == "")
			{
				e.preventDefault();
				$fieldThatCannotBeEmpty.parent().next().fadeIn();
			}

		});

	});
	
}



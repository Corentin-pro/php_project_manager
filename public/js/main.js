var overlay_listener;

// Showing modal that should be showed (use of 'modal-pre-show' class so the javascript trigers the animation)
var pre_show_modal = document.querySelector('.modal-pre-show'); // querySelector select the first encounter only
if( pre_show_modal )
{
	pre_show_modal.classList.remove('modal-pre-show');
	pre_show_modal.classList.add('modal-show');

	var overlay = document.getElementsByClassName('modal-overlay')[0];
	if(overlay_listener)
	{
		overlay.removeEventListener( 'click', overlay_listener );
	}
	overlay_listener = function removeModalHandler()
	{
		pre_show_modal.classList.remove('modal-show');
		overlay.removeEventListener( 'click', overlay_listener );
	}
	overlay.addEventListener( 'click', overlay_listener );
}

// Handy for date
function doubleDigitNumber(number)
{
  return (number < 10 ? '0' : '') + number;
}
// Parsing UTC date to convert into the client timezone
function parseDate()
{
	var date_elements = document.getElementsByClassName('date'); // scan all the tag with the class 'date'
	if( date_elements )
	{
		var date_elements_count = date_elements.length
		var date;
		var date_info;
		for( var i = 0 ; i < date_elements_count ; i++ )
		{
			// Split timestamp into [ Y, M, D, h, m, s,... ]
			date_info = date_elements[i].innerHTML.split(/[- :]/);

			if( 5 < date_info.length )
			{
				//date = new Date(Date.UTC(date_info[0], date_info[1]-1, date_info[2], date_info[3], date_info[4], date_info[5]));
				date = new Date( Date.UTC(date_info[0], date_info[1] - 1, date_info[2], date_info[3] || 0, date_info[4] || 0, date_info[5] || 0) );
				date_elements[i].innerHTML = date.getFullYear() + "-" + doubleDigitNumber(date.getMonth() + 1) + "-" + doubleDigitNumber(date.getDate())
					+ "&nbsp;" + doubleDigitNumber(date.getHours()) + ":" +doubleDigitNumber( date.getMinutes()) + ":" + doubleDigitNumber(date.getSeconds());
			}
		}
	}
}
parseDate();

function showModal(modal_id)
{
	var modal = document.getElementById(modal_id);
	if( !modal.classList.contains('modal-show') )
	{
		modal.classList.add('modal-show');

		var overlay = document.getElementsByClassName('modal-overlay')[0];
		if(overlay_listener)
		{
			overlay.removeEventListener( 'click', overlay_listener );
		}
		overlay_listener = function removeModalHandler()
		{
			removeModal( modal_id );
		}
		overlay.addEventListener( 'click', overlay_listener );
	}
}

function removeModal(modal_id)
{
	var modal = document.getElementById(modal_id);
	if( modal.classList.contains('modal-show') )
	{
		modal.classList.remove('modal-show');

		var overlay = document.getElementsByClassName('modal-overlay')[0];
		if(overlay_listener)
		{
			overlay.removeEventListener( 'click', overlay_listener );
		}
	}
}

function showError(p_message)
{
	var modal_error = document.getElementById('modal-error');
	var modal_error_message = modal_error.querySelector('p');
	if( modal_error && modal_error_message )
	{
		modal_error_message.innerHTML = p_message;
		showModal('modal-error');
	}
	else
	{
		alert(p_message);
	}
}

function submitForm(form_id)
{
	var form = document.getElementById(input_id);
	if( form )
	{
		form.submit();
	}
}

function updateInput(value,input_id)
{
	var input = document.getElementById(input_id);
	if( input )
	{
		input.value = value;
	}
}

function updateElementHtml(value,element_id)
{
	var element = document.getElementById(element_id);
	if( element )
	{
		element.innerHTML = value;
	}
}

function callAPIInModalWithElement(url,modal_id,element_id)
{
	url = url + '&js=%E2%9C%93'; // Set the api to return a response for javascript (✓, hex: e2 9c 93 or 2713)
	var element = document.getElementById(element_id);
	if( element )
	{
		var xhttp = new XMLHttpRequest();
		xhttp.onreadystatechange = function()
		{
			if (this.readyState == 4 && this.status == 200)
			{
				try
				{
					var json = JSON.parse(this.responseText);
					if( 'ok' === json['status'] )
					{
						element.innerHTML = json['message'];
						showModal(modal_id);
					}
					else
					{
						showError( json['message'] );
					}
				}
				catch(error)
				{
					showError( error + '<br /><br />Data :<br />' + this.responseText );
				}
			}
		};
		xhttp.open("GET", url, true);
		xhttp.send();
	}
}

function loadCardsInElement(url,element_id)
{
	var element = document.getElementById(element_id);
	if( element )
	{
		var cardCount = element.childElementCount;
		var xhttp = new XMLHttpRequest();
		xhttp.onreadystatechange = function()
		{
			if (this.readyState == 4 && this.status == 200)
			{
				try
				{
					var json = JSON.parse(this.responseText);
					if( 'ok' === json['status'] )
					{
						resetProgress();
						element.innerHTML += json['message']; // This breaks all the canvas/element list
						initProgress();
						parseDate();
					}
					else
					{
						showError( json['message'] );
					}
				}
				catch(error)
				{
					showError( error + '<br /><br />Data :<br />' + this.responseText );
				}
			}
		};
		xhttp.open("GET", url + "&offset=" + cardCount, true);
		xhttp.send();
	}
}

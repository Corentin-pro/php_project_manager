/*
This script will parse all the 'pre-progress' class and extract the info inside to make an animation
The progress syntax should be 'current/total unit' where 'current' and 'total' are integers and 'unit' a string ( / and space as separators)
During 'initProgress' :
The 'pre-progress' will draw a full background circle if 'current' or 'total' is 0.
If not the informations will be store in the 'progress_array' variable and the element will be given a 'in-progress' class
At the end 'initProgress' will call 'showProgress'.
During 'showProgress';
The canvas of each 'in-progress' element will be updated until the 'progress_frame' reach 'progress_total_frame_count'.
Before ending all 'in-progress' class will be removed.

At any time 'initProgress' can be called again to animated newly created 'pre-progress' elements.
*/

const progress_total_frame_count = 50;
var progress_frame; // Current frame count
var progress_array = new Array(); // Progress element to pprocess
var progress_array_all = new Array(); // Process element (when resizing all needs to be processed again)
var progress_resize_timeout;

// Reset the progress div before they were parsed by the JS
// (this is used as a workaround as adding new divs with innerHTML breaks all the canvas, should be fixed)
function resetProgress()
{
	if( 0 < progress_array_all.length )
	{
		for( var i = 0 ; i < progress_array_all.length ; i++ )
		{
			progress_array_all[i].element.classList.add('pre-progress');
			progress_array_all[i].element.innerHTML = progress_array_all[i].progress_current + "/" + progress_array_all[i].progress_total + " " + progress_array_all[i].progress_unit
		}
	}
}

// Initialise progress element
function initProgress()
{
	progress_frame = 1; // Starting at frame 1

	var progress_elements = document.getElementsByClassName('pre-progress'); // scan all the tag with the class 'pre-progress'
	if( progress_elements )
	{
		// The different variables used in the loop are instanciate here (for performance)
		var progress_object;
		var progress_info;
		var progress_total;
		var progress_current;
		var canvas;
		var ctx;

		// Initialise the progress divs
		while( 0 < progress_elements.length )
		{
			progress_info = progress_elements[0].innerHTML.split(/[/ ]/);

			// Clearing the DOM
			progress_elements[0].innerHTML = '';

			// Adding the canvas and span
			canvas = document.createElement('canvas');
			progress_elements[0].appendChild(canvas);
			span = document.createElement('span');
			progress_elements[0].appendChild(span);

			canvas.width = progress_elements[0].clientWidth;
			canvas.height = 100;
			progress_elements[0].style.height = '100px';

			// Initialise canvas context
			ctx = canvas.getContext('2d');
			ctx.translate(canvas.width / 2, canvas.height / 2); // change center
			ctx.rotate( -0.5 * Math.PI ); // rotate -90 deg

			// if no progress we can stop here (no need to be processed futher)
			progress_current = parseFloat(progress_info[0]);
			progress_total = parseFloat(progress_info[1]);

			progress_object = {
				element : progress_elements[0],
				canvas : canvas,
				ctx : ctx,
				span : span,
				progress_current : progress_current,
				progress_total : progress_total,
				progress_unit : progress_info[2],
			};
			if( (0 === progress_current) || (0 === progress_current) )
			{
				ctx.beginPath();
				ctx.arc(0, 0, 40, 0, Math.PI * 2, false);
				ctx.strokeStyle = '#aaa';
				ctx.lineCap = 'butt'; // butt, round or square
				ctx.lineWidth = 10;
				ctx.stroke();

				span.innerHTML = '0%<br/>(' + progress_current + ' / ' + progress_total + progress_info[2] + ')';
			}
			else
			{
				progress_array.push(progress_object);
				// Adding the 'in-progress' class to be processed
				progress_elements[0].classList.add('in-progress');
			}

			progress_array_all.push(progress_object);

			// Removing the 'pre-progress' class
			progress_elements[0].classList.remove('pre-progress');
		}
	}

	showProgress();
}

// Process the in-progress element (draw the circular progress)
function showProgress()
{
	if( 0 < progress_array.length )
	{
		// The different variables used in the loop are instanciate here (for performance)
		var progress_percent;
		var ctx;

		for( var i = 0 ; i < progress_array.length ; i++ )
		{
			// Calculate progress (factoring the frame count)
			progress_array[i].progress_percent = (progress_frame / progress_total_frame_count)
				* (progress_array[i].progress_current / progress_array[i].progress_total);

			// Drawing
			ctx = progress_array[i].ctx;
			ctx.clearRect(-progress_array[i].canvas.width / 2,
				-progress_array[i].canvas.height / 2,
				progress_array[i].canvas.width,
				progress_array[i].canvas.height); // clear the canvas (transltated because of the arc function)

			// Drawing the background full circle
			ctx.beginPath();
			ctx.arc(0, 0, 40, 0, Math.PI * 2, false);
			ctx.strokeStyle = '#aaa';
			ctx.lineCap = 'butt'; // butt, round or square
			ctx.lineWidth = 10;
			ctx.stroke();

			// Drawing the progress circle
			ctx.beginPath();
			ctx.arc(0, 0, 40, 0, Math.PI * 2 * progress_array[i].progress_percent, false);
			ctx.strokeStyle = 'hsl(' + (125 * progress_array[i].progress_percent * progress_array[i].progress_percent) + ', 50%, 50%)';
			ctx.lineCap = 'round'; // butt, round or square
			ctx.lineWidth = 7;
			ctx.stroke();

			progress_array[i].span.innerHTML = ( Math.round(progress_array[i].progress_percent * 10000) / 100 ) + '%<br/>('
				+ progress_array[i]. progress_current + ' / ' + progress_array[i].progress_total + progress_array[i].progress_unit + ')';
		}
	}

	// Checking if the animation is over
	if( (0 < progress_array.length) && (progress_total_frame_count > progress_frame) )
	{
		// If not over increase the frame count and request next frame
		progress_frame++;
		requestAnimationFrame(showProgress);
	}
	else
	{
		// If over removing the 'in-progress' classes
		for( var i = 0 ; i < progress_array.length ; i++ )
		{
			progress_array[i].element.classList.remove('in-progress');
		}
		// Clearing progress_array
		progress_array = [];
	}
}

// Redraw the progress (for resize event)
function resizeProgress()
{
	if( 0 < progress_array_all.length )
	{
		// The different variables used in the loop are instanciate here (for performance)
		var progress_percent;
		var canvas;
		var ctx;

		for( var i = 0 ; i < progress_array_all.length ; i++ )
		{
			// Reseting the canvas width
			progress_array_all[i].canvas.width = progress_array_all[i].element.clientWidth;

			// Recreating the canvas context
			progress_array_all[i].ctx = progress_array_all[i].canvas.getContext('2d');
			progress_array_all[i].ctx.translate(progress_array_all[i].canvas.width / 2, progress_array_all[i].canvas.height / 2); // change center
			progress_array_all[i].ctx.rotate( -0.5 * Math.PI ); // rotate -90 deg

			progress_array_all[i].element.classList.add('in-progress');
			progress_array.push( progress_array_all[i] );
		}
	}

	showProgress();
}

// Timeout for resizeProgress (to smoothly resize the window)
function prepareResizeProgress()
{
	window.clearTimeout(progress_resize_timeout);
	progress_resize_timeout = window.setTimeout(resizeProgress, 50);
}

initProgress();

window.onresize = prepareResizeProgress;

// Queue for smallBox notifications
const smallBoxQueue = [];

// Function to process the queue when `divSmallBoxes` exists
function processSmallBoxQueue() {
    const divSmallBoxes = document.getElementById('divSmallBoxes');
    if (divSmallBoxes) {
        // Fire all pending notifications in the queue
        while (smallBoxQueue.length > 0) {
            const { type, msg, title, isSticky } = smallBoxQueue.shift(); // Remove the first notification in the queue
            fireSmallBox(type, msg, title, isSticky);
        }
        // Debug check queue : Uncomment to check if queue is empty and divSmallBoxes exists
        //const isQueueProcessed = smallBoxQueue.length === 0 && document.getElementById('divSmallBoxes');
        //console.log(isQueueProcessed ? "Queue is empty and divSmallBoxes exists" : "Queue has items or divSmallBoxes is missing");
    } else {
        // Check again after a delay
        setTimeout(processSmallBoxQueue, 100); // Retry every 100ms
    }
}

// Function to fire a smallBox notification
function fireSmallBox(type, msg, title = '', isSticky = false) {
    type = type.toLowerCase();

    let color, icon, content;
    switch (type) {
        case 'success':
            color = '#739E73';
            icon = 'fa fa-check';
            break;
        case 'error':
            color = '#C46A69';
            icon = 'fa fa-exclamation';
            break;
        case 'info':
            color = '#3276B1';
            icon = 'fa fa-info-circle';
            break;
        case 'warning':
            color = '#C79121';
            icon = 'fa fa-exclamation';
            break;
        default:
            color = '#3276B1'; // Default to info
            icon = 'fa fa-info-circle';
    }
    content = '<i>' + msg + '</i>';

    const smallBoxConfig = {
        content: content,
        color: color,
        sound: false,
        iconSmall: icon,
        timeout: isSticky ? 0 : 5000 // Make sticky if isSticky is true
    };

    // Include title only if provided
    if (title) {
        smallBoxConfig.title = title;
    }

    $.smallBox(smallBoxConfig);
}

// notifyMsg function to handle smallBox or fallback to other methods
function notifyMsg(type, msg, title = '', isSticky = false) {
    if (typeof $.smallBox === 'function') {
        const divSmallBoxes = document.getElementById('divSmallBoxes');
        if (divSmallBoxes) {
            // If `divSmallBoxes` exists, fire immediately
            fireSmallBox(type, msg, title, isSticky);
        } else {
            // Otherwise, queue the notification
            smallBoxQueue.push({ type, msg, title, isSticky });
            processSmallBoxQueue(); // Start checking for `divSmallBoxes`
        }
    } else if (typeof toastr !== 'undefined' && typeof toastr[type] === 'function') {
        toastr.options.timeOut = isSticky ? 0 : 5000; // Make sticky if isSticky is true
        toastr.options.extendedTimeOut = isSticky ? 0 : 1000; // Prevent auto-dismiss on hover if sticky
        toastr[type](msg, title ? title : undefined);
    } else {
        alert(msg); // Fallback to an alert
    }
}

// Function to stop intervals
function stopIntervals(...intervalIds) {
    intervalIds.forEach(intervalId => {
        if (intervalId) {
            clearInterval(intervalId); // Stop each interval
        }
    });
}

// Function for thousands separator and decimal formatting
function numberFormat(number, decimals = 0, decPoint = '.', thousandsSep = ',') {
    if (isNaN(number) || number === null) return '';

    number = parseFloat(number).toFixed(decimals);

    let parts = number.split('.');
    let integerPart = parts[0];
    let decimalPart = parts[1] || '';

    // Add thousands separator
    integerPart = integerPart.replace(/\B(?=(\d{3})+(?!\d))/g, thousandsSep);

    return decimals > 0 ? integerPart + decPoint + decimalPart : integerPart;
}

<script>
	function uncheckOther(otherCheckboxId, currentCheckbox) {
		const otherCheckbox = document.getElementById(otherCheckboxId);
		if (currentCheckbox.checked) {
			otherCheckbox.checked = false;
		}
	}

	document.addEventListener('DOMContentLoaded', function() {
		//var set_budget = "{{ Input::old('set_budget') ?? (isset($set_budget) ? $set_budget : '') }}";
		var setBudgetSelect = document.getElementById('set_budget');
        const budgetSections = document.querySelectorAll('.budget_section');
        const customBidCheckbox = document.getElementById('enable_custom_bid_value');

        budgetSections.forEach(el => {
            el.style.display = (setBudgetSelect.value === '1') ? 'block' : 'none';
        });

        setBudgetSelect.addEventListener('change', function() {
            budgetSections.forEach(el => {
                el.style.display = (this.value === '1') ? 'block' : 'none';
            });
        });

		const percentCheckbox = document.getElementById('bid_decrement_percent');
		const valueCheckbox = document.getElementById('bid_decrement_value');
		const percentInput = document.getElementById('decrement_percent');
		const valueInput = document.getElementById('decrement_value');

		percentInput.disabled = false;
		valueInput.disabled = false;
		percentInput.required = true;
		valueInput.required = true;

		// Function to disable/enable percent input based on the checkbox
		function updatePercentInput() {
			if (percentCheckbox.checked) {
				percentInput.disabled = true;
				percentInput.required = false;  // Remove required when disabled
				//percentInput.value = 0;
				valueInput.disabled = false;
				valueInput.required = true;  // Ensure value input is required when percent is disabled
			} else {
				percentInput.disabled = false;
				percentInput.required = true;
			}
		}

		// Function to disable/enable value input based on the checkbox
		function updateValueInput() {
			if (valueCheckbox.checked) {
				valueInput.disabled = true;
				valueInput.required = false;  // Remove required when disabled
				//valueInput.value = 0;
				percentInput.disabled = false;
				percentInput.required = true;  // Ensure percent input is required when value is disabled
			} else {
				valueInput.disabled = false;
				valueInput.required = true;
			}
		}

		// Initial check on page load
		updatePercentInput();
		updateValueInput();

		// Add event listeners to checkboxes
		percentCheckbox.addEventListener('change', updatePercentInput);
		valueCheckbox.addEventListener('change', updateValueInput);

		// Bid mode handling
        const bidModeSelect = document.getElementById('bid_mode');

        function updateBidModeLabels(bidMode) {
            const decrementLabels = document.querySelectorAll('.bid-label-decrement');
            const incrementLabels = document.querySelectorAll('.bid-label-increment');

            switch (bidMode) {
                case "{{ \PCK\EBiddings\EBiddingMode::BID_MODE_INCREMENT }}":   // Increment
                    decrementLabels.forEach(label => label.style.display = 'none');
                    incrementLabels.forEach(label => label.style.display = 'block');
                    break;
                default:    // Default - Decrement
                    decrementLabels.forEach(label => label.style.display = 'block');
                    incrementLabels.forEach(label => label.style.display = 'none');
            }
        }

        function updateBidModeContainers(bidMode) {
            const hideOtherBidderInfo = document.querySelector('.row.hide-other-bidder-info');
            //const budgetContainer = document.querySelector('.row.budget');
            const percentageContainer = document.querySelector('.row.percentage');
            const fixedAmountContainer = document.querySelector('.row.fixed-amount');
            const customAmountContainer = document.querySelector('.row.custom-amount');

            switch (bidMode) {
                case "{{ \PCK\EBiddings\EBiddingMode::BID_MODE_ONCE }}":   // Zones
                    hideOtherBidderInfo.style.display = 'block';
                    //budgetContainer.style.display = 'none';
                    percentageContainer.style.display = 'none';
                    fixedAmountContainer.style.display = 'none';
                    customAmountContainer.style.display = 'none';
                    if (customBidCheckbox) {
                        customBidCheckbox.checked = true; // Check custom bid checkbox
                        toggleCustomBidValue(); // Show custom bid value container
                    }
                    break;
                default:    // Default - Others
                    hideOtherBidderInfo.style.display = 'none';
                    //budgetContainer.style.display = 'block';
                    percentageContainer.style.display = 'block';
                    fixedAmountContainer.style.display = 'block';
                    customAmountContainer.style.display = 'block';
                    if (customBidCheckbox) {
                        customBidCheckbox.checked = false; // Uncheck custom bid checkbox
                        toggleCustomBidValue(); // Hide custom bid value container
                    }
            }
        }

        function toggleBidMode() {
            let bidMode = (bidModeSelect) ? bidModeSelect.value : null;

            updateBidModeLabels(bidMode);
            updateBidModeContainers(bidMode);
        }

        toggleBidMode();

		if (bidModeSelect) {
			bidModeSelect.addEventListener('change', toggleBidMode);
		}

        function toggleCustomBidValue() {
            let bidMode = (bidModeSelect) ? bidModeSelect.value : null;

            const noTieBidContainer = document.querySelector('.row.no-tie-bid');
            const minBidAmountDiffContainer = document.querySelector('.row.min-bid-amount-diff');

            if (customBidCheckbox.checked) {
                if (bidMode === "{{ \PCK\EBiddings\EBiddingMode::BID_MODE_ONCE }}") {
                    noTieBidContainer.style.display = 'block';
                }
                minBidAmountDiffContainer.style.display = 'block';
            } else {
                noTieBidContainer.style.display = 'none';
                minBidAmountDiffContainer.style.display = 'none';
            }
        }

        if (customBidCheckbox) {
            customBidCheckbox.addEventListener('change', toggleCustomBidValue);
        }
	});
</script>
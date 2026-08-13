/* Sales invoice builder (vanilla JS, no frameworks).
 *
 * Powers the New Sale page:
 *   - debounced AJAX product search (GET, small JSON responses)
 *   - adding a product as a line item (duplicates merge into one row)
 *   - editable quantities with live line totals
 *   - live subtotal / discount / grand total
 *   - client-side validation and a double-submit guard
 *
 * The backend remains authoritative: every monetary value is
 * recalculated from the database on save.
 */
(function () {
	'use strict';

	var form = document.getElementById('sale-form');

	if (form === null) {
		return;
	}

	var searchWrap = document.getElementById('product-search');
	var searchInput = document.getElementById('product-search-input');
	var resultsBox = document.getElementById('product-results');
	var tbody = document.getElementById('invoice-items');
	var emptyState = document.getElementById('invoice-empty');
	var subtotalEl = document.getElementById('summary-subtotal');
	var summaryDiscountEl = document.getElementById('summary-discount');
	var totalEl = document.getElementById('summary-total');
	var discountInput = document.getElementById('discount');
	var errorBox = document.getElementById('invoice-error');
	var errorMessage = document.getElementById('invoice-error-message');
	var saveButton = document.getElementById('save-invoice-btn');
	var saving = false;

	var searchUrl = searchWrap.getAttribute('data-search-url') || 'sales/search-products';
	var warehouseSelect = document.getElementById('warehouse_id');
	var searchTimer = null;
	var activeRequest = null;

	// Changing the warehouse changes the stock shown, so re-run an
	// in-progress search against the newly selected warehouse.
	warehouseSelect.addEventListener('change', function () {
		var term = searchInput.value.trim();

		if (term.length >= 2) {
			clearTimeout(searchTimer);
			searchTimer = setTimeout(function () {
				performSearch(term);
			}, 250);
		}
	});

	/* ------------------------------------------------------------------ *
	 * Helpers
	 * ------------------------------------------------------------------ */

	function formatMoney(value) {
		return '$' + Number(value || 0).toFixed(2);
	}

	function showError(message) {
		errorMessage.textContent = message;
		errorBox.classList.remove('hidden');
		errorBox.classList.add('flex');
	}

	function hideError() {
		errorBox.classList.add('hidden');
		errorBox.classList.remove('flex');
		errorMessage.textContent = '';
	}

	/* ------------------------------------------------------------------ *
	 * Product search
	 * ------------------------------------------------------------------ */

	searchInput.addEventListener('input', function () {
		clearTimeout(searchTimer);
		hideError();

		var term = searchInput.value.trim();

		if (term.length < 2) {
			hideResults();
			return;
		}

		searchTimer = setTimeout(function () {
			performSearch(term);
		}, 250);
	});

	function performSearch(term) {
		// Drop the previous request so responses can never arrive out of order.
		if (activeRequest !== null) {
			activeRequest.abort();
		}

		// Stock is per warehouse, so search the selected one (if any).
		var url = searchUrl + '?q=' + encodeURIComponent(term);
		var warehouseId = warehouseSelect.value;

		if (warehouseId !== '') {
			url += '&warehouse_id=' + encodeURIComponent(warehouseId);
		}

		var request = fetch(url, {
			method: 'GET',
			headers: {
				'X-Requested-With': 'XMLHttpRequest',
				'Accept': 'application/json'
			}
		});

		activeRequest = request;

		request.then(function (response) {
			if ( ! response.ok) {
				throw new Error('Search request failed');
			}
			return response.json();
		}).then(function (data) {
			if (activeRequest === request) {
				activeRequest = null;
				renderResults(data.products || []);
			}
		}).catch(function () {
			if (activeRequest === request) {
				activeRequest = null;
			}
		});
	}

	function renderResults(products) {
		resultsBox.textContent = '';

		if (products.length === 0) {
			var none = document.createElement('li');
			none.className = 'px-3.5 py-2.5 text-sm text-slate-500';
			none.textContent = 'No products found.';
			resultsBox.appendChild(none);
		} else {
			products.forEach(function (product, index) {
				var item = document.createElement('li');

				var button = document.createElement('button');
				button.type = 'button';
				button.className = 'flex w-full items-center justify-between gap-3 px-3.5 py-2.5 text-left text-sm transition hover:bg-indigo-50 focus:bg-indigo-50 focus:outline-none';
				button.setAttribute('role', 'option');
				button.setAttribute('data-index', String(index));
				button.dataset.productId = String(product.id);
				button.dataset.productName = product.name;
				button.dataset.productCode = product.code || '';
				button.dataset.productPrice = String(product.price);

				var left = document.createElement('span');
				left.className = 'flex min-w-0 flex-col';

				var label = document.createElement('span');
				label.className = 'font-medium text-slate-900';
				label.textContent = product.name;
				left.appendChild(label);

				// Show the current stock in the selected warehouse when the
				// search knew which warehouse to look in.
				if (product.stock !== null && product.stock !== undefined) {
					var stock = document.createElement('span');
					stock.className = 'text-xs ' + (product.stock > 0 ? 'text-emerald-700' : 'text-red-600');
					stock.textContent = product.stock > 0 ? 'In stock: ' + product.stock : 'Out of stock';
					left.appendChild(stock);
				}

				var right = document.createElement('span');
				right.className = 'flex shrink-0 items-center gap-2';

				if (product.code) {
					var code = document.createElement('span');
					code.className = 'text-xs text-slate-400';
					code.textContent = product.code;
					right.appendChild(code);
				}

				var price = document.createElement('span');
				price.className = 'text-sm font-semibold text-slate-700';
				price.textContent = formatMoney(product.price);
				right.appendChild(price);

				button.appendChild(left);
				button.appendChild(right);
				item.appendChild(button);

				button.addEventListener('click', function () {
					addProduct({
						id: button.dataset.productId,
						name: button.dataset.productName,
						code: button.dataset.productCode,
						price: Number(button.dataset.productPrice)
					});
				});

				resultsBox.appendChild(item);
			});
		}

		resultsBox.classList.remove('hidden');
	}

	function hideResults() {
		resultsBox.classList.add('hidden');
		resultsBox.textContent = '';
	}

	// Close the dropdown on outside click or Escape.
	document.addEventListener('click', function (event) {
		if ( ! searchWrap.contains(event.target)) {
			hideResults();
		}
	});

	document.addEventListener('keydown', function (event) {
		if (event.key === 'Escape' && ! resultsBox.classList.contains('hidden')) {
			hideResults();
		}
	});

	// Minimal keyboard navigation: Enter adds the highlighted/first result,
	// ArrowDown / ArrowUp move between results.
	searchInput.addEventListener('keydown', function (event) {
		if (resultsBox.classList.contains('hidden')) {
			return;
		}

		var options = resultsBox.querySelectorAll('[data-index]');

		if (options.length === 0) {
			return;
		}

		var current = resultsBox.querySelector('[data-index].bg-indigo-50');
		var currentIndex = current !== null ? Number(current.getAttribute('data-index')) : -1;
		var nextIndex;

		if (event.key === 'Enter') {
			event.preventDefault();
			var target = current !== null ? current : options[0];
			target.click();
			return;
		}

		if (event.key === 'ArrowDown') {
			event.preventDefault();
			nextIndex = (currentIndex + 1) % options.length;
		} else if (event.key === 'ArrowUp') {
			event.preventDefault();
			nextIndex = (currentIndex - 1 + options.length) % options.length;
		} else {
			return;
		}

		options.forEach(function (option) {
			option.classList.remove('bg-indigo-50');
		});

		options[nextIndex].classList.add('bg-indigo-50');
	});

	/* ------------------------------------------------------------------ *
	 * Invoice items
	 * ------------------------------------------------------------------ */

	function addProduct(product) {
		hideError();

		var existing = tbody.querySelector('tr[data-product-id="' + product.id + '"]');

		if (existing !== null) {
			// Same product again: bump the existing row's quantity.
			var qtyInput = existing.querySelector('.qty-input');
			qtyInput.value = String(Number(qtyInput.value || 1) + 1);
			updateRow(existing);
		} else {
			var row = document.createElement('tr');
			row.dataset.productId = product.id;
			row.dataset.price = String(product.price);

			// Product (name + code + hidden id for the form).
			var productCell = document.createElement('td');
			productCell.className = 'px-6 py-4';

			var name = document.createElement('span');
			name.className = 'block text-sm font-medium text-slate-900';
			name.textContent = product.name;
			productCell.appendChild(name);

			if (product.code) {
				var code = document.createElement('span');
				code.className = 'block text-xs text-slate-400';
				code.textContent = product.code;
				productCell.appendChild(code);
			}

			var hiddenId = document.createElement('input');
			hiddenId.type = 'hidden';
			hiddenId.name = 'product_id[]';
			hiddenId.value = product.id;
			productCell.appendChild(hiddenId);

			// Quantity (editable form field).
			var qtyCell = document.createElement('td');
			qtyCell.className = 'px-6 py-4';

			var qtyInput = document.createElement('input');
			qtyInput.type = 'number';
			qtyInput.name = 'quantity[]';
			qtyInput.value = '1';
			qtyInput.min = '1';
			qtyInput.step = '1';
			qtyInput.inputMode = 'numeric';
			qtyInput.className = 'qty-input block w-20 rounded-lg border-0 px-3 py-2 text-sm text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600';
			qtyInput.addEventListener('input', function () {
				updateRow(row);
			});
			qtyCell.appendChild(qtyInput);

			// Price.
			var priceCell = document.createElement('td');
			priceCell.className = 'price-cell whitespace-nowrap px-6 py-4 text-right text-sm text-slate-700';
			priceCell.textContent = formatMoney(product.price);

			// Line total.
			var totalCell = document.createElement('td');
			totalCell.className = 'total-cell whitespace-nowrap px-6 py-4 text-right text-sm font-semibold text-slate-900';
			totalCell.textContent = formatMoney(product.price);

			// Remove action.
			var actionCell = document.createElement('td');
			actionCell.className = 'whitespace-nowrap px-6 py-4 text-right text-sm font-medium';

			var removeButton = document.createElement('button');
			removeButton.type = 'button';
			removeButton.className = 'text-red-600 transition hover:text-red-500';
			removeButton.textContent = 'Remove';
			removeButton.addEventListener('click', function () {
				removeRow(row);
			});
			actionCell.appendChild(removeButton);

			row.appendChild(productCell);
			row.appendChild(qtyCell);
			row.appendChild(priceCell);
			row.appendChild(totalCell);
			row.appendChild(actionCell);

			tbody.appendChild(row);

			// Compute the initial line total (qty 1 x price) and refresh the summary.
			updateRow(row);
		}

		// Reset the search box so the next product can be typed right away.
		searchInput.value = '';
		hideResults();
		searchInput.focus();
		toggleEmptyState();
	}

	function removeRow(row) {
		row.remove();
		recalcSummary();
		toggleEmptyState();
	}

	function updateRow(row) {
		var qty = Number(row.querySelector('.qty-input').value);
		var price = Number(row.dataset.price);
		var total = (Number.isFinite(qty) && qty >= 0 && Number.isFinite(price)) ? qty * price : 0;

		row.dataset.lineTotal = String(total);
		row.querySelector('.total-cell').textContent = formatMoney(total);
		recalcSummary();
	}

	function toggleEmptyState() {
		if (tbody.querySelector('tr') === null) {
			emptyState.classList.remove('hidden');
		} else {
			emptyState.classList.add('hidden');
		}
	}

	/* ------------------------------------------------------------------ *
	 * Summary
	 * ------------------------------------------------------------------ */

	function recalcSummary() {
		var subtotal = 0;

		tbody.querySelectorAll('tr').forEach(function (row) {
			subtotal += Number(row.dataset.lineTotal || 0);
		});

		// The discount is a fixed amount; never a negative one.
		var discount = parseFloat(discountInput.value);
		discount = Number.isFinite(discount) && discount > 0 ? discount : 0;

		var total = subtotal - discount;
		if (total < 0) {
			total = 0;
		}

		subtotalEl.textContent = formatMoney(subtotal);
		summaryDiscountEl.textContent = formatMoney(discount);
		totalEl.textContent = formatMoney(total);
	}

	discountInput.addEventListener('input', function () {
		hideError();
		recalcSummary();
	});

	// Hide the error banner as soon as the user starts fixing the form.
	form.addEventListener('input', hideError);

	/* ------------------------------------------------------------------ *
	 * Submit
	 * ------------------------------------------------------------------ */

	function validateForm() {
		var customer = document.getElementById('customer_id').value;
		var warehouse = document.getElementById('warehouse_id').value;
		var rows = tbody.querySelectorAll('tr');
		var discount = parseFloat(discountInput.value);

		if (customer === '') {
			showError('Please choose a customer.');
			return false;
		}

		if (warehouse === '') {
			showError('Please choose a warehouse.');
			return false;
		}

		if (rows.length === 0) {
			showError('Please add at least one product to the invoice.');
			return false;
		}

		for (var i = 0; i < rows.length; i++) {
			var qty = Number(rows[i].querySelector('.qty-input').value);

			if ( ! Number.isInteger(qty) || qty < 1) {
				showError('Product quantities must be whole numbers of at least 1.');
				return false;
			}
		}

		if (Number.isFinite(discount) && discount < 0) {
			showError('The discount cannot be negative.');
			return false;
		}

		return true;
	}

	form.addEventListener('submit', function (event) {
		event.preventDefault();

		if (saving) {
			return;
		}

		if ( ! validateForm()) {
			return;
		}

		// Prevent accidental double submission while the invoice is being saved.
		saving = true;
		saveButton.disabled = true;
		saveButton.textContent = 'Saving…';

		form.submit();
	});
})();

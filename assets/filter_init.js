(()=>{
	// prepare
	let firstHeader = document.querySelector('h2');
	if (!firstHeader) {
		return;
	}
	firstHeader.insertAdjacentHTML('beforebegin', '<div id="filter-controls-container">');

	// filter
	const ViewFilter = ViewFilter_hashed_a40934580jldhfj084957lhgldf;

	// define view filter (do this at any time)
	let listFilter = new ViewFilter();
	// on load create controls and pre-parse items
	let controlsSelector = "#filter-controls-container", 
		itemsSelector = "li";
	listFilter.init(controlsSelector, itemsSelector);
})();
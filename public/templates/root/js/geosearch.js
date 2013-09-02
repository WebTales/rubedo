function initialize() {
	// retrieve params
	var blockConfig = JSON.parse(jQuery(".gmapmapcanvas")
			.attr("data-mapparams"));
	var prefix = jQuery(".gmapmapcanvas").attr("data-prefix");
	// define map options
	var mapOptions = {
		center : new google.maps.LatLng(48.8567, 2.3508),
		zoom : blockConfig.zoom || 12,
		mapTypeId : google.maps.MapTypeId.ROADMAP
	};
	// initialize map and geocoder
	var map = new google.maps.Map(document
			.getElementById(prefix + "map_canvas"), mapOptions);
	var geocoder = new google.maps.Geocoder();
	// set up places serach system if option is active
	if (blockConfig.showPlacesSearch) {
		var input = /** @type {HTMLInputElement} */
		(document.getElementById(prefix + 'target'));
		var searchBox = new google.maps.places.SearchBox(input);
		google.maps.event.addListener(searchBox, 'places_changed', function() {
			var places = searchBox.getPlaces();
			map.setCenter(places[0].geometry.location);
			if (blockConfig.zoomOnAddress) {
				map.setZoom(blockConfig.zoomOnAddress);
			} else {
				map.setZoom(14);
			}

		});
		google.maps.event.addListener(map, 'bounds_changed', function() {
			var bounds = map.getBounds();
			searchBox.setBounds(bounds);
		});
	}
	// set up marker clustering
	var usedMarkers = [];
	var markerCluster = new MarkerClusterer(map, usedMarkers, {
		batchSize : 20000,
		averageCenter : false,
		gridSize : 60,
		batchSizeIE : 20000
	});
	// redefine cluster counter to take multi-content markers into account
	markerCluster.setCalculator(function(a, b) {
		var total = 0;
		for ( var i = 0; i < a.length; i++) {
			total = total + a[i].count;
		}
		for ( var c = 0, f = total, g = f; g !== 0;)
			g = parseInt(g / 10, 10), c++;
		c = Math.min(c, b);
		return {
			text : f,
			index : c
		}
	});
	// extract params for map behaviour
	var useLocation = blockConfig.useLocation;
	var centerAddress = blockConfig.centerAddress;
	var centerLatitude = blockConfig.centerLatitude;
	var centerLongitude = blockConfig.centerLongitude;
	var activateSearch = blockConfig.activateSearch;
	var showCenterMarker = blockConfig.showCenterMarker;
	var predefinedFacets = blockConfig.predefinedFacets;
	var displayedFacets = blockConfig.displayedFacets;
	var displayMode = blockConfig.displayMode;
	displayedFacets = displayedFacets;
	var facetOverrides = blockConfig.facetOverrides;
	var pagesize = blockConfig.pageSize;
	// set initial map position (geoloc or address or coordinates in this order)
	if (useLocation && navigator.geolocation) {
		navigator.geolocation.getCurrentPosition(function(position) {
			var initialLocation = new google.maps.LatLng(
					position.coords.latitude, position.coords.longitude);
			map.setCenter(initialLocation);
			if (showCenterMarker) {
				new google.maps.Marker({
					map : map,
					icon : "/templates/root/img/target.png",
					position : initialLocation
				});
			}
		}, function() {
			console.log("geolocation Error");
		});

	} else if (centerAddress) {

		geocoder.geocode({
			'address' : centerAddress
		}, function(results, status) {
			if (status == google.maps.GeocoderStatus.OK) {
				map.setCenter(results[0].geometry.location);
				if (showCenterMarker) {
					new google.maps.Marker({
						map : map,
						icon : "/templates/root/img/target.png",
						position : results[0].geometry.location
					});
				}
			}
		});
	} else if (centerLatitude && centerLongitude) {
		map.setCenter(new google.maps.LatLng(centerLatitude, centerLongitude));
		if (showCenterMarker) {
			new google.maps.Marker({
				map : map,
				icon : "/templates/root/img/target.png",
				position : new google.maps.LatLng(centerLatitude,
						centerLongitude)
			});
		}
	}
	// set up data fetch timer and auxilary arrays
	var mapTimer = null;
	google.maps.event.addListener(map, 'bounds_changed', function() {
		clearTimeout(mapTimer);
		mapTimer = setTimeout(function() {
			fetchData();
		}, 300);
	});
	window.activeFacets = {};

	var oldPositions = [];
	var newPositions = [];
	window.fireQuery = fetchData;
	// main data retrieval function
	function fetchData() {
		// get params and fire request
		var bounds = map.getBounds();
		var params = {
			'current-page' : jQuery('body').attr('data-current-page'),
			'pagesize' : pagesize,
			'constrainToSite' : blockConfig.constrainToSite,
			'predefinedFacets' : predefinedFacets,
			'displayedFacets' : displayedFacets,
			'facetOverrides' : facetOverrides,
			'inflat' : bounds.getSouthWest().lat(),
			'suplat' : bounds.getNorthEast().lat(),
			'inflon' : bounds.getSouthWest().lng(),
			'suplon' : bounds.getNorthEast().lng(),
			'displayMode' : displayMode,
			'autoComplete' : blockConfig.autoComplete,
		}
		var currentFacets = window.activeFacets;
		for ( var attrname in currentFacets) {
			params[attrname] = currentFacets[attrname];
		}
		var request = jQuery.ajax({
			url : window.location.protocol + '//' + window.location.host
					+ '/blocks/geo-search/xhr-search',
			type : "POST",
			data : params,
			dataType : "json"
		});

		request
				.done(function(data) {
					// calculate which markers to keep and which to remove, send
					// new markers to content handler, rerender facets and
					// active facets
					oldPositions = [];
					newPositions = [];
					for ( var j = 0; j < usedMarkers.length; j++) {
						oldPositions.push(usedMarkers[j].entityId);
					}
					var recievedFacets = data.activeFacets;
					window.activeFacets = {};
					for ( var m = 0; m < recievedFacets.length; m++) {
						if ((recievedFacets[m].id == "author")
								|| (recievedFacets[m].id == "date")) {
							window.activeFacets[recievedFacets[m].id] = recievedFacets[m].terms[0].term;
						} else {
							var intermed = [];
							for ( var p = 0; p < recievedFacets[m].terms.length; p++) {
								intermed.push(recievedFacets[m].terms[p].term);
							}
							window.activeFacets[recievedFacets[m].id] = intermed;

						}
					}
					if (activateSearch) {
						jQuery("#facetBox").replaceWith(data.facetsHtml);
						jQuery("#activeFacetBox").replaceWith(
								data.activeFacetsHtml);
					}
					var rezArray = data.data;
					for ( var i = 0; i < rezArray.length; i++) {
						handleContent(rezArray[i].position_location,
								rezArray[i].title, rezArray[i].id,
								rezArray[i].idArray, rezArray[i].count);
					}
					var newUsed = [];
					for ( var u = 0; u < usedMarkers.length; u++) {
						if (newPositions.indexOf(usedMarkers[u].entityId) == -1) {
							markerCluster.removeMarker(usedMarkers[u], true);
							usedMarkers[u].setMap(null);
						} else {
							newUsed.push(usedMarkers[u]);
						}
					}
					usedMarkers = [];
					usedMarkers = newUsed;
					// repaint cluster only after calculations are done
					markerCluster.repaint();
					// reatach events for checkbox mode and autocomplete
					jQuery('.facetCheckbox').click(
							function() {
								if (jQuery(this).prop("checked")) {
									window.updateFacets(jQuery(this).attr(
											"data-facet-id"), jQuery(this)
											.attr("data-facet-term"), true);
								} else {
									window.updateFacets(jQuery(this).attr(
											"data-facet-id"), jQuery(this)
											.attr("data-facet-term"), false);
								}
							})
					$('.typeahead')
							.typeahead(
									{
										source : function(query, process) {
											var request = jQuery
													.ajax({
														url : '/blocks/geo-search/xhr-get-suggests',
														type : "POST",
														data : {
															'query' : query,
															'current-page' : jQuery(
																	'body')
																	.attr(
																			'data-current-page'),
															'searchParams' : jQuery(
																	'#searchpage')
																	.attr(
																			'data-searchparams')
														},
														dataType : "json"
													});
											request.done(function(data) {
												return process(data.terms);
											});

										},
										matcher : function() {
											return true;
										},
										items : 10,
										minLength : 3
									})
				});

		request.fail(function(jqXHR, textStatus) {
			console.log("failed to fetch data");
		});
	}
	// facet updating function, modifies active facets and fires the query
	window.updateFacets = updateFacets;
	function updateFacets(id, term, add) {
		if ((id == "author") || (id == "date") || (id == "query")) {

			if (add) {
				window.activeFacets[id] = term;
			} else {
				delete window.activeFacets[id];
			}
		} else {
			if (add) {
				if (Array.isArray(window.activeFacets[id])) {
					if (window.activeFacets[id].indexOf(term) == -1) {
						window.activeFacets[id].push(term);
					}
				} else {
					window.activeFacets[id] = new Array(term);
				}

			} else {
				if (window.activeFacets[id].length == 1) {
					delete window.activeFacets[id];
				} else {
					var myInterIndex = window.activeFacets[id].indexOf(term);
					window.activeFacets[id].splice(myInterIndex, 1);
				}
			}
		}
		window.fireQuery();
	}
	// content handler, formats params and passes them to marker builder
	function handleContent(contentPosition, title, entityId, idArray, count) {
		contentPosition = contentPosition.split(",");
		if (contentPosition[0] && contentPosition[1]) {
			createContentMarker(new google.maps.LatLng(contentPosition[0],
					contentPosition[1]), title, entityId, idArray, count);
		}
	}
	var activeInfoWindows = [];
	// content marker creator
	function createContentMarker(location, title, entityId, idArray, count) {
		// register with auxilary arrays, check if not existent and build
		newPositions.push(entityId);
		if (oldPositions.indexOf(entityId) == -1) {
			var marker = new google.maps.Marker({
				map : map,
				icon : "/templates/root/img/map_pin.png",
				position : location,
				title : "" + title,
				count : count,
				entityId : entityId,
				idArray : idArray
			});
			usedMarkers.push(marker);
			markerCluster.addMarker(marker, true);
			// add info display listener
			google.maps.event.addListener(marker, 'click', function() {
				//close other info windows
				for ( var p = 0; p < activeInfoWindows.length; p++) {
					activeInfoWindows[p].close();
				}
				activeInfoWindows = [];
				// detail retriever request
				var request2 = jQuery.ajax({
					url : window.location.protocol + '//'
							+ window.location.host
							+ '/blocks/geo-search/xhr-get-detail',
					type : "POST",
					data : {
						'current-page' : jQuery('body').attr(
								'data-current-page'),
						'idArray' : marker.idArray
					},
					dataType : "json"
				});

				request2.done(function(data) {
					//display and register infowindow
					var infowindow = new google.maps.InfoWindow({
						content : data.data
					});

					activeInfoWindows.push(infowindow);
					infowindow.open(map, marker);

				});

				request2.fail(function(jqXHR, textStatus) {
					console.log("failed to fetch detail");
				});

			});
		}

	}
	// handle places search field positionn if it is used
	if (blockConfig.showPlacesSearch) {
		function placeField() {
			var mapCanvasWidth = document.getElementById(prefix + "map_canvas").clientWidth;
			var fieldContainer = document
					.getElementById(prefix + "holderpanel");
			var field = document.getElementById(prefix + "target");

			if (mapCanvasWidth < 430) {
				var width = mapCanvasWidth - 35;

				fieldContainer.style.width = (width * 0.7) + "px";

				field.style.width = (width * 0.7) + "px";

				var fieldMargin = ((mapCanvasWidth - field.clientWidth) / 2) - 35;

				if (fieldMargin > 0) {
					fieldContainer.style.marginLeft = (fieldMargin + 35) + "px";
				}
			} else {
				var width = mapCanvasWidth - 75;

				fieldContainer.style.width = (width * 0.7) + "px";

				field.style.width = (width * 0.7) + "px";

				var fieldMargin = ((mapCanvasWidth - field.clientWidth) / 2) - 75;

				if (fieldMargin > 0) {
					fieldContainer.style.marginLeft = (fieldMargin + 75) + "px";
				}
			}
		}

		placeField();
		window.onresize = function() {
			placeField();
		};
	}

}

window.onload = initialize;
var gMap = function (options,id,title,text,field) {
	//Create Class attr
	gMap.instances.push(this); // Add map into static array of map instances
		var self=this;
		this.options=Object();
		this.field=(field=="true")?true:false;
		this.title=title;
		this.text=text;
		this.map;
		this.options=options;
		this.id=id;
		this.zoom=(options.length>1)?3:14;
		this.useLocation=(options.useLocation)?options.useLocation:false;
		this.markerToEdit;
		this.geocoder=new google.maps.Geocoder();
		this.markers=new Array();

	//Constructor call
		this.initialize();
	};
	// class prototype
	gMap.prototype = {
		initialize:function() {
			var self=this;
			var mapOptions={
					center: new google.maps.LatLng(48.8567, 2.3508),
			        zoom: self.zoom,
			        mapTypeId: google.maps.MapTypeId.ROADMAP
			};

			self.map = new google.maps.Map(document.getElementById(self.id),
			            mapOptions);
			if(self.useLocation){
				//Get user location, create marker at position and setCenter of map on user icon
				this.addUserMarker();
			      }
		/*
		 * 
		 * If more than one marker on the map
		 * 
		 */
			 
		 if(self.options.length==null){
			 self.options=[self.options];
		 }
			jQuery(self.options).each(function(){
				var marker=this;
				if (this.address) {
			      	self.geocoder.geocode( { 'address': this.address}, function(results, status) {
			      		if (status == google.maps.GeocoderStatus.OK) {
			      			self.addMarker(marker,self.title,self.text);
			      			self.map.setCenter(results[0].geometry.location);
			      		}else {
			    		   
		    		    }
			      	});
			      } else if (this.lat && this.lon){
			    	self.addMarker(this,self.title,self.text);
			    	  self.map.setCenter(new google.maps.LatLng(this.lat,this.lon));
			    	  
			    	  this.geocoder.geocode({'latLng': new google.maps.LatLng(this.lat,this.lon)}, function(results, status) {
			    		    
			    		    if (status == google.maps.GeocoderStatus.OK) {
			    		     if (results[1]) {
			    		      self.address=results[1].formatted_address;
			    		     }
			    		    } else {
			    		    	  console.log("geocodage failed :" +status);
			    		    }
			    		   });
			      }
			});
				
			
			
		},
		getValues:function(){
			var self=this;
			if(self.markers.length>1){
				var values=Object();
				self.markers.forEach(function(marker){
					values[self.markers.indexOf(marker)]={
						"address" : marker.position.address, 
						"location" : { "type" : "Point" , "coordinates" : [ marker.position.kb, marker.position.jb]},
						"lat" : marker.position.jb,
						"lon" : marker.position.kb
					}
					
				});
			}else{
			var values={ 
					"address" : self.markers[0].position.address, 
					"location" : { "type" : "Point" , "coordinates" : [ self.markers[0].position.kb ,self.markers[0].position.jb]},
					"lat" : self.markers[0].position.jb,
					"lon" : self.markers[0].position.kb
				}
			}
			return values;
		},
		createMarker:function(location,title,contentString,f_address){
			var self=this;
			marker = new google.maps.Marker({
			  	map:self.map,
			  	position:location,
			  	title:title
			});
			marker.position.address=f_address;
			self.markers.push(marker);
		  var infowindow = new google.maps.InfoWindow({
	          content: contentString         
	       });

		  google.maps.event.addListener(marker, 'click', function() {
			  infowindow.open(self.map,this);
	      });
		  google.maps.event.addListener(marker, 'rightclick', function() {
			 self.markerToEdit=this;
			jQuery("#"+self.id+"-edit .latitude").val(this.position.jb);
  			jQuery("#"+self.id+"-edit .longitude").val(this.position.kb);
  			jQuery("#"+self.id+"-edit .address").val(this.position.address);
		    });
		},
		addMarker:function(location,title,contentString) {
			var self=this;
			if(self.field==true){self.deleteAllMarkers();}
			if (location.address){
				
	    		  self.geocoder.geocode( { 'address': location.address}, function(results, status) {
	    	      		if (status == google.maps.GeocoderStatus.OK) {
	    	      			self.createMarker(new google.maps.LatLng(results[0].geometry.location.jb,results[0].geometry.location.kb), title, contentString,results[0].formatted_address);
	    	      			self.map.setCenter(new google.maps.LatLng(results[0].geometry.location.jb,results[0].geometry.location.kb));
	    	      			if(self.options.length==1){
			      			jQuery("#"+self.id+"-edit .latitude").val(results[0].geometry.location.jb);
			    			jQuery("#"+self.id+"-edit .longitude").val(results[0].geometry.location.kb);
			    			jQuery("#"+self.id+"-edit .address").val(results[0].formatted_address);
			    			}
	  
	    	      		}else {
		    		    	  console.log("geocodage failed :" +status);
		    		    }
	    	      	});  
	    	  } else if (location.latitude && location.longitude){
	    		 self.createMarker(new google.maps.LatLng(location.latitude,location.longitude), title, contentString); 
	    		 self.map.setCenter(new google.maps.LatLng(location.latitude,location.longitude));
	    		 this.geocoder.geocode({'latLng': new google.maps.LatLng(location.latitude,location.longitude)}, function(results, status) {
		    		    
		    		    if (status == google.maps.GeocoderStatus.OK) {
		    		     if (results[1]) {
		    		 		if(self.options.length==1){
		    		 			jQuery("#"+self.id+"-edit .latitude").val(results[0].geometry.location.jb);
				    			jQuery("#"+self.id+"-edit .longitude").val(results[0].geometry.location.kb);
				    			jQuery("#"+self.id+"-edit .address").val(results[0].formatted_address);
				    			}
		    		     }
		    		    } else {
		    		    	  console.log("geocodage failed :" +status);
		    		    }
		    		   });
	          }
			return true;
			
		},
		addUserMarker:function(){// Add marker on user position if geolocation is enabled on his navigator
			var self=this;
			if(navigator.geolocation)
				{
				navigator.geolocation.getCurrentPosition(function(position) {
				      userLocation = new google.maps.LatLng(position.coords.latitude,position.coords.longitude);
				      	
					      new google.maps.Marker({
						  	map:self.map,
						  	position:userLocation,
						  	title:"You are here",
						  	icon:"https://maps.gstatic.com/mapfiles/ms2/micons/man.png"
						      });
					      
					    }, function() {
						      console.log("geolocation Error");
					    });
				}else{
					console.log("Your navigator disable geolocation.");
				}
		},
		deleteAllMarkers:function(){
			var self=this;
			self.markers.forEach(function(marker){
				marker.setMap(null);
			});
			self.markers=new Array();
		},
		deleteMarker:function(gmId)
		{
			var self=this;
			self.markers.forEach(function(marker){
				if(marker.__gm_id==gmId)
					{
					self.markers.splice(self.markers.indexOf(marker),1).sort();
					marker.setMap(null);
					}
			});
		}
	}
	gMap.instances=new Array();
	gMap.getAllInstances=function(){
		return gMap.instances;
	}
	gMap.findInstance=function(id){
		var instance=null;
		gMap.instances.forEach(function(map){
			if(map.id==id)
			{
				instance=map;
			}
		});
		return instance;
	}
	gMap.mapRefresh=function(id,marker){
		//Add input values
		if(marker==null){
		var newAddress=jQuery("#"+id+"-edit .address").val();
		var newLat=jQuery("#"+id+"-edit .latitude").val();
		var newLong=jQuery("#"+id+"-edit .longitude").val();
		this.map=gMap.findInstance(id);
		var self=this;
		this.address=newAddress;
		this.latitude=newLat;
		this.longitude=newLong;
		this.location={
				address:this.address,
				latitude:this.latitude,
				longitude:this.longitude
		}
		if(this.map.markers.length>1){
			if(this.map.markerToEdit){
				this.map.deleteMarker(this.map.markerToEdit.__gm_id);
				this.map.addMarker(this.location,this.map.title,this.map.text);
			}else{
				jQuery("#"+id+"-error-msg").show();
				jQuery("#"+id+"-error-msg").html("Please select a marker by right click.");
			}
			
		}
		else{
			this.map.addMarker(this.location,this.map.title,this.map.text);
		}
		
		}

	};
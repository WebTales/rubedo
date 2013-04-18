var gMap = function (options,id,title,text,field) {
	//Create Class attr
	gMap.instances.push(this); // Add map into static array of map instances
		var self=this;
		this.field=(field=="true")?true:false;
		this.title=title;
		this.text=text;
		this.map;
		this.options=(typeof options =="string")?JSON.parse(options):options;
		this.id=id;
		this.useLocation=(options.useLocation)?options.useLocation:true;
		this.address=this.options.address;
		this.latitude=this.options.latitude;
		this.longitude=this.options.longitude;
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
			        zoom: 14,
			        mapTypeId: google.maps.MapTypeId.ROADMAP
			};

			self.map = new google.maps.Map(document.getElementById(self.id),
			            mapOptions);
			if(self.useLocation){
				//Get user location, create marker at position and setCenter of map on user icon
				this.addUserMarker();
			      }
			if (self.address) {
			      	this.geocoder.geocode( { 'address': self.address}, function(results, status) {
			      		if (status == google.maps.GeocoderStatus.OK) {
			      			self.addMarker(self.options,self.title,self.text);
			      			self.map.setCenter(results[0].geometry.location);
			      			self.latitude=results[0].geometry.location.kb;
			      			self.longitude=results[0].geometry.location.lb;
			      		}else {
			    		   
		    		    }
			      	});
			      } else if (self.latitude && self.longitude){
			    		self.addMarker(self.options,self.title,self.text);
			    	  self.map.setCenter(new google.maps.LatLng(self.latitude, self.longitude));
			    	  
			    	  this.geocoder.geocode({'latLng': new google.maps.LatLng(self.latitude,self.longitude)}, function(results, status) {
			    		    
			    		    if (status == google.maps.GeocoderStatus.OK) {
			    		     if (results[1]) {
			    		      self.address=results[1].formatted_address;
			    		     }
			    		    } else {
			    		    	  console.log("geocodage failed :" +status);
			    		    }
			    		   });
			      }
			
		},
		getValues:function(){
			var self=this;
			var opt={
					address:self.address,
					latitude:self.latitude,
					longitude:self.longitude
			}
			return opt;
		},
		createMarker:function(location,title,contentString){
			var self=this;
			marker = new google.maps.Marker({
			  	map:self.map,
			  	position:location,
			  	title:title
			});
			self.markers.push(marker);
		  var infowindow = new google.maps.InfoWindow({
	          content: contentString         
	       });

		  google.maps.event.addListener(marker, 'click', function() {
			  infowindow.open(self.map,this);
	      });
		},
		addMarker:function(location,title,contentString) {
			var self=this;
			if(self.field==true){self.deleteAllMarkers();}
			if (location.address){
	    		  self.geocoder.geocode( { 'address': location.address}, function(results, status) {
	    	      		if (status == google.maps.GeocoderStatus.OK) {
	    	      			console.log(results);
	    	      			self.createMarker(new google.maps.LatLng(results[0].geometry.location.jb,results[0].geometry.location.kb), title, contentString);
	    	      			self.map.setCenter(new google.maps.LatLng(results[0].geometry.location.jb,results[0].geometry.location.kb));
	    	      			self.latitude=results[0].geometry.location.jb;
			      			self.longitude=results[0].geometry.location.kb;
			      			self.address=results[0].formatted_address;
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
		    		    	 self.address=results[1].formatted_address;
		    		    	 self.latitude=location.latitude;
				      		self.longitude=location.longitude;
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
	gMap.mapRefresh=function(id){
		//Add input values
		var newAddress=jQuery("#"+id+"-edit .address").val();
		var newLat=jQuery("#"+id+"-edit .latitude").val();
		var newLong=jQuery("#"+id+"-edit .longitude").val();
		this.map=gMap.findInstance(id);
		var self=this;
		this.address=newAddress;
		this.latitude=newLat;
		this.longitude=newLong;
		/*this.address=(newAddress!="")?newAddress:this.map.address;
		this.latitude=(newLat!="")?newLat:this.map.latitude;
		this.longitude=(newLong!="")?newLong:this.map.longitude;*/
		this.location={
				address:this.address,
				latitude:this.latitude,
				longitude:this.longitude
		}
		
		this.map.addMarker(this.location,this.map.title,this.map.text);
			/*jQuery("#"+id+"-edit .latitude").val(self.map.latitude);
			jQuery("#"+id+"-edit .longitude").val(self.map.longitude);
			jQuery("#"+id+"-edit .address").val(self.map.address);*/
		//console.log(this.map);
				
	   
	
		
	};
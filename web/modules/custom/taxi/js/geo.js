// Function to Work with Google Map(JQuery).
(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.myGoogleMapBehavior = {
    // Map Initialization by the API.
    attach: function initMap(context, settings) {
      // $('body').css('background', color_body);

      // Get Location by Clicking a Button for it.
      $('.location-button').click(function () {
        // LookingThrough Navigator API.
        if  ("geolocation" in navigator) {
          // Trying to Get Location(if, NOT, We'll Get a Message in Console).
          navigator.geolocation.getCurrentPosition(foundLocation, noFoundLocation, options);
        }
      });
        // Options for Location Object(Accuracy and etc).
      const options = {
        enableHighAccuracy: true,
        timeout: 5000,
        maximumAge: 2000
      };
      // Successful Response.
      function foundLocation(pos) {
        // Coord object.
        let crd = pos.coords;
        let lat = crd.latitude;
        let lng = crd.longitude;
        // Setting a Value in Form for Latitude and Longitude.
        $(".taxi-latitude").val(lat);
        $(".taxi-longitude").val(lng);
        // Create a Marker on Map.
        const startCoord = new google.maps.LatLng(lat, lng);
        const start = new google.maps.Marker({
          position: startCoord,
          map,
          title: 'You Are Here!'
        });
      }
      // Failed Response.
      function noFoundLocation(error) {
        // If Troubles with Permissions to Location.
        if (error.PERMISSION_DENIED) {
          console.log('ACCESS DENIED TO PERFORM LOCATION REQUEST' + error);
        }
        // Other Problems.
        else {
          console.log('YOUR BROWSER DOES NOT SUPPORT THIS LOCATION REQUEST' + error);
        }
      }
      // Map Initialization.
      // The Lutsk CityCenter is Center of the Map.
      // PS: Working with Google API.
      let lutsk = new google.maps.LatLng(50.7472, 25.3254);
      let mapOptions = {
        zoom: 12,
        center: lutsk
      };
      // Creating a Map by API.
      let map = new google.maps.Map(document.getElementById('map'), mapOptions);
      // To Set Up Directions.
      let directionsDisplay = new google.maps.DirectionsRenderer;
      let directionsService = new google.maps.DirectionsService;
      directionsDisplay.setMap(map);

      // Coordinates from Earlier Session(for AJAX, Get that by drupalSettingAPI).
      // And Set a Marker on Previous Place.
      let latitude = drupalSettings.taxi.latitude;
      let longitude = drupalSettings.taxi.longitude;
      if (latitude !== null && longitude !== null) {
        const startCoord = new google.maps.LatLng(parseFloat(latitude),parseFloat(longitude));
        const start = new google.maps.Marker({
          position: startCoord,
          map,
          title: 'You Are Here!'
        });
      }

      // function initialize() {
      //   var lutsk = new google.maps.LatLng(50.7472, 25.3254);
      //   var mapOptions = {
      //     zoom: 13,
      //     center: lutsk
      //   };
      //   map = new google.maps.Map(document.getElementById('map'), mapOptions);
      //   directionsDisplay.setMap(map);
      //   // google.maps.event.addDomListener(document.getElementById('routebtn'), 'click', calcRoute);
      // }

      // function calcRoute() {
      //   var start = new google.maps.LatLng(50.7472, 25.3254);
      //   //var end = new google.maps.LatLng(38.334818, -181.884886);
      //   var end = new google.maps.LatLng(37.441883, -122.143019);
      //   var request = {
      //     origin: start,
      //     destination: end,
      //     travelMode: google.maps.TravelMode.DRIVING
      //   };
      //   directionsService.route(request, function (response, status) {
      //     if (status === google.maps.DirectionsStatus.OK) {
      //       directionsDisplay.setDirections(response);
      //       directionsDisplay.setMap(map);
      //     } else {
      //       alert("Directions Request from " + start.toUrlValue(6) + " to " + end.toUrlValue(6) + " failed: " + status);
      //     }
      //   });
      // }
      //
    }
  };
}(jQuery, Drupal, drupalSettings));

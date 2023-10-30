<!doctype html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>Presensi</title>
		<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
		<style>
			video {width: 100%; height: auto;}
		</style>
	</head>
	<body>
		<main class="container my-3">
			<div class="row">
				<div class="col-md-5 mb-3 mb-md-0">
					<video width="400" autoplay="true" id="video-webcam">
						Browsermu tidak mendukung bro, upgrade donk!
					</video>
				</div>
				<div class="col-md-7">
					<form class="form">
						<div class="input-group input-group-lg mb-3">
							<input type="text" name="id" class="form-control form-control-lg" placeholder="Masukkan ID" autofocus required>
							<button class="btn btn-lg btn-primary" type="submit">Submit</button>
						</div>
						<input type="hidden" name="long" id="long" class="form-control" readonly>
						<input type="hidden" name="lat" id="lat" class="form-control" readonly>
						<textarea name="lokasi" class="form-control d-none" rows="3" readonly></textarea>
					</form>
					<img id="snapshot" class="d-none">
					<table class="table table-borderless d-none">
						<tr>
							<td width="100">ID</td>
							<td width="10">:</td>
							<td id="id"></td>
						</tr>
						<tr>
							<td>NAMA</td>
							<td>:</td>
							<td>John Doe</td>
						</tr>
						<tr>
							<td>WAKTU</td>
							<td>:</td>
							<td id="timestamp"></td>
						</tr>
						<tr>
							<td>LOKASI</td>
							<td>:</td>
							<td id="lokasi"></td>
						</tr>
					</table>
				</div>
			</div>
		</main>
		<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4" crossorigin="anonymous"></script>
		<script src="https://code.jquery.com/jquery-3.7.0.min.js" integrity="sha256-2Pmvv0kuTBOenSvLm6bvfBSSHrUJ+3A7x6P5Ebd07/g=" crossorigin="anonymous"></script>
		<script type="text/javascript">
			// seleksi elemen video
			var video = document.querySelector("#video-webcam");

			// minta izin user
			navigator.getUserMedia = navigator.getUserMedia || navigator.webkitGetUserMedia || navigator.mozGetUserMedia || navigator.msGetUserMedia || navigator.oGetUserMedia;

			// jika user memberikan izin
			if (navigator.getUserMedia) {
				// jalankan fungsi handleVideo, dan videoError jika izin ditolak
				navigator.getUserMedia({ video: true }, handleVideo, videoError);
			}

			// fungsi ini akan dieksekusi jika  izin telah diberikan
			function handleVideo(stream) {
				video.srcObject = stream;
			}

			// fungsi ini akan dieksekusi kalau user menolak izin
			function videoError(e) {
				// do something
				alert("Izinkan menggunakan webcam untuk presensi!");
			}
			
			let Longitude = 0, Latitude = 0;
			getLocation();
			
			// Get Current Location 
			function getLocation() {
				if(navigator.geolocation) {
					window.setInterval(() => {
						navigator.geolocation.getCurrentPosition(showPosition);
					}, 1000);
				} else { 
					alert("Geolocation is not supported by this browser.");
				}
			}

			function showPosition(position) {
				document.getElementById("long").value = position.coords.longitude;
				document.getElementById("lat").value = position.coords.latitude;
				if(Longitude != position.coords.longitude.toFixed(3) || Latitude != position.coords.latitude.toFixed(3)) {
					getLocationName(position.coords.longitude, position.coords.latitude);
					Longitude = position.coords.longitude.toFixed(3);
					Latitude = position.coords.latitude.toFixed(3);
				}
			}

			function getLocationName(lon, lat) {
				$.ajax({
					type: 'get',
					url: 'https://nominatim.openstreetmap.org/reverse',
					data: {format: 'jsonv2', lon: lon, lat: lat},
					success: function(response) {
						$("textarea[name=lokasi]").val(response.display_name);
					},
					error: function() {
						alert("Tidak dapat menampilkan lokasi. Mohon refresh halaman ini!");
						window.location.reload();
					}
				});
			}
			
			function takeSnapshot() {
				// buat elemen img
				var img = document.querySelector("#snapshot");
				var context;

				// ambil ukuran video
				var width = video.offsetWidth;
				var height = video.offsetHeight;

				// buat elemen canvas
				canvas = document.createElement('canvas');
				canvas.width = width;
				canvas.height = height;

				// ambil gambar dari video dan masukan 
				// ke dalam canvas
				context = canvas.getContext('2d');
				context.drawImage(video, 0, 0, width, height);

				// render hasil dari canvas ke elemen img
				img.src = canvas.toDataURL('image/png');
				img.classList.add("d-none");
				
				document.querySelector("#id").innerHTML = document.querySelector("input[name=id]").value;
				document.querySelector("#timestamp").innerHTML = getTimestamp();
				document.querySelector("#lokasi").innerHTML = document.querySelector("textarea[name=lokasi]").value;
				document.querySelector(".table").classList.remove("d-none");
			}
			
			function getTimestamp() {
				// ambil waktu sekarang
				var month = ["Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];
				var now = new Date();
				var time = now.getDate() + " " + month[now.getMonth()] + " " + now.getFullYear() + ", " + addZero(now.getHours()) + ":" + addZero(now.getMinutes()) + ":" + addZero(now.getSeconds()) + " WIB";
				
				return time;
			}
			
			function addZero(value) {
				if(value < 10)
					return "0" + value;
				else
					return value;
			}
		</script>
		<script>
			$(document).on("submit", ".form", function(e){
				e.preventDefault();
				takeSnapshot();
				document.querySelector("input[name=id]").value = null;
			});
		</script>
	</body>
</html>
<h1>Google</h1>	<ul>		<li>			<a href="https://console.developers.google.com/project" target="_blank">https://console.developers.google.com/project</a>		</li>		<li>			<a href="https://www.portalgas.it/google/examples/index.php" target="_blank">https://www.portalgas.it/google/examples/index.php</a>		</li>		<li>portalgas.it@gmail.com - <?php echo Configure::read('pwd');?></li></ul><h1>Google App</h1>	<ul>		<li>			<a href="https://play.google.com/apps/publish/" target="_blank">https://play.google.com/apps/publish/</a> portalgas.it@gmail.com - <?php echo Configure::read('pwd');?>		</li>		<li>			<a href="https://play.google.com/store/apps/details?id=com.ionicframework.portalgas" target="_blank">https://play.google.com/store/apps/details?id=com.ionicframework.portalgas</a>		</li>		<li>			<a href="http://wallet.google.com/merchant" target="_blank">http://wallet.google.com/merchant</a>		</li>		<li>			<a href="https://admin.google.com/" target="_blank">https://admin.google.com/</a>		</li>		<li>			<a href="https://payments.google.com/" target="_blank">https://payments.google.com/</a>		</li>		<li>			<a href="https://support.google.com/a/" target="_blank">https://support.google.com/a/</a>		</li>		<li>			<a href="https://support.google.com/merchants/" target="_blank">https://support.google.com/merchants/</a>		</li>				</ul><pre class="shell" rel="cmd build apk">	cd C:\Users\f.actisgrosso\AppData\Roaming\npm\myAppionic build --release android -Xlint:deprecationcd C:\Users\f.actisgrosso\AppData\Roaming\npm\myApp\platforms\android\build\"C:\Program Files\Java\jdk1.8.0_60\bin\keytool.exe" -genkey -v -keystore portalgas-key.keystore -alias portalgas -keyalg RSA -keysize 2048 -validity 10000CN=fractis, OU=pal, O=PortAlGas, L=turin, ST=TO, C=IT "C:\Program Files\Java\jdk1.8.0_60\bin\jarsigner.exe" -verbose -sigalg SHA1withRSA -digestalg SHA1 -keystore portalgas-key.keystore "C:\Users\f.actisgrosso\AppData\Roaming\npm\myApp\platforms\android\build\outputs\apk\android-release-unsigned.apk" portalgas"C:\Program Files (x86)\Android\android-sdk\build-tools\22.0.1\zipalign.exe" -v 4 "C:\Users\f.actisgrosso\AppData\Roaming\npm\myApp\platforms\android\build\outputs\apk\android-release-unsigned.apk" PortAlGas.apkleggere il certificato "C:\Program Files\Java\jdk1.8.0_60\bin\keytool.exe" -list -v -keystore "C:\Users\f.actisgrosso\AppData\Roaming\npm\myApp\platforms\android\build\portalgas-key.keystore" -alias portalgas -storepass portalgas -keypass portalgas "C:\Program Files\Java\jdk1.8.0_60\bin\jarsigner.exe"  -verify -certs -verbose "C:\Users\f.actisgrosso\AppData\Roaming\npm\myApp\platforms\android\build\PortAlGas.apk"  </pre><p>Cambiare numero di versione</p><pre class="shell" rel="config.xml"	&lt;?xml version="1.0" encoding="UTF-8" standalone="yes"?&gt;	&lt;widget id="com.ionicframework.portalgas" versionCode="2" version="consultazione" 		android-versionCode="2"		ios-CFBundleVersion="2"		xmlns="http://www.w3.org/ns/widgets" xmlns:cdv="http://cordova.apache.org/ns/1.0"&gt;</pre>
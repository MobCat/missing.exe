# missing.exe
Lightweight and easy to install "anonymous" file sharing host.<br>
![index](https://cdn.discordapp.com/attachments/1063759326340186172/1147707728777715792/image.png) ![file](https://cdn.discordapp.com/attachments/1063759326340186172/1147708467138801714/image.png)
```diff
- !WARNING!: Not safe, please read.
- !WARNING!: Not safe, please read.
- !WARNING!: Not safe, please read.
```
This implementation is just a proof of concept by someone who has no idea what they are doing.<br>
It contains several security and implementation vulnerabilities.<br>
DO NOT PUSH THIS CODE TO PROD! EVER!<br>
It's more meant as a code example and an idea of how small a "pop up street stall" file host can be.<br><br>
You make a web server, you unpack the missing folder into it. This folder contains 2 php scripts, some file icons and a folder where files go.<br>
That's it. No sql, no accounts, no complex downloading and installing 37 repositories inside a docker container.<br>
You ftp 2 php files to any random web site, now it's a file host.<br>Done.<br><br>

"pop up street stall style" as in you can make a file sharing website anywhere in under a minute. it is very easy to install and run.<br>
Because of this ease of use and the host having almost no idea what files are on there server, there are no admin tools or ways to banned / remove users or content.<br>
You can delete files from the server if you need to free up space, but you will have no idea what you are deleting.<br>
This also means that there is no api to upload files. Some sort of curl api would be cool, but way to easy to abuse, especially for small files size like this.<br>
The small file size is meant as a fun throw back, it is the size of a 3.5" High density floppy. People use to cram all kinds of things onto those back in the day.<br>
It also makes managing it a little easier as it's hundreds of people uploading small files, rather then hundreds of people uploading large files thing.<br>
Your data usage over time will be less, but still a pain to manage without some sort of admin tool. And adding said tools may remove some of the anonymity of the site.<br><br>

"anonymous" as in I really wanted this to be something like anonfiles (rip 😿) where you can just upload something, without an account, and it's just there.<br>
However without the "we will auto delete whatever you upload in 90 days or less" thing. I just wanted a place to put random files without messing about with them or constantly checking if they are still online or not.<br>
Dump'n'go. No need to worry about it ever, as long as the server is still up, the private key is still the same, the link should still be valid.<br>
However, because I have no idea what i'm doing, ALL of the actions of this site are handled SERVER SIDE.<br>
If you know anything about encryption, you know this is super trash. Like really bad.<br>
php is server side only. So you upload a file to the server, it encrypts it and stores it.<br>
You request a file, it decrypts it and sends it to you.<br>
Did you catch that? The server is in charge of encrypting and decrypting the files.<br>
So even know the files are stored encrypted, as soon as a valid link is given to decrypt it, that decryption is handled server side.<br>
Both the checking how big a file is an encrypting it is also handled server side too.<br>
So a "plain text" file is sent over the net, then checked and encrypted on the server.<br>
It would be way to easy to "honey pot" this and just syphon off all the files people upload and download to a new folder that is not encrypted.<br>
or some how get around the max file size thing as that is also checked server side *after* you upload something.<br>
We *should* be doing this encryption and decryption locally on the clients side, but idk how to do that with out invoking the hell spawn that is JavaScript..<br>
Might be able to do something in html5 but I like that this site works fine on an early 2000's web browser. It fits the whole vibe of the site and how I make things.<br>
No need for flashy css or site animations. Quick, easy and fast. It "looking bad" is just an aesthetic choice. That I like.<br>
Anyways enough rambling<br>

# How to install and use
Like I said, this is very easy and quick to setup.<br>
Just download the ![missing-release.7z](https://github.com/MobCat/missing.exe/releases/download/beta/missing-release.0.5.7z), unpack the `missing` folder into the root of your webserver.<br>
Then navigate to it like `http://127.0.0.1/missing` and you can now upload a file from there.<br>
In the `missing` folder there is a `info.html` you might like to edit to tell users about your instance of the missing file host and who you are.<br>
```diff
- !IMPORTANT!: You need to change the default private key.
```
Todo this, edit the `file.php` and `index.php` scripts, at the top of the scripts you will see a `$key = ` variable. You need to change this to some other 30+ special char string.<br>
This will ensure that you can't just move files from one host to another and they will still work. Also means your links only work on your site.<br>
When editing this key, you need to make sure they are set to the same thing. `index.php` will encrypt your file, `file.php` will decrypt it. And they can't do that 
if they do not have the same key.<br><br>

Now you have that all setup, you can go to the index page, click on browse to select a file from your computer, select something that is <= 1.44 MB.<br>
Then click open. The filename should show next to the browse button, then you can just click upload.<br>
As we are uploading a MB of data, this will happen quite quickly.<br>
Once done, you will be taken to a new page, that has your link<br>
`http://127.0.0.1/missing/file?v=BygiSyAvG3YjfUhuOC4OSg8mGAYNeAEaR0kFDIziyjrgbbqikLp4ZNJqk8w`<br>
Now you can either click on the copy to clipboard button to well copy it, or just click on it to open it.<br>
This will now bring you to the `file.php` script where you can see a little preview icon, the file name and file size, the MD5 checksum of the file, and our download button.<br>
Just click the button to download it.<br>
The file will now be fetched, decrypted and sent to you.<br>

# TODO:
(well outside of making this thing "real" and actually useful)<br>
Make some sort of `setup.php` script that will auto generate the private keys, stash them somewhere not on the root of the website, and then delete it's self.<br><br>
As we are using a whole new file and padding it to "hide" it for file metadata, it would be awesome to allow for some sort of nfo file saving.<br>
So you can save your scene / crack info with the file upload. And it would be displayed just under the file info and icon when you open the link.<br><br>
Ok I did say outside of making this thing actually good, But we really do need to find a way to check file sizes locally *before* uploading them.<br>

# Conclusion
I know this implementation is bad, and it probably won't scale very well if say a thousand people tried to download things at once<br>
But everyone has to start somewhere, and I wanted something small and lightweight to learn on.<br>
It would be really awesome if I was smart enough to make this thing actually functional and launch it as a real site. But time, skill and money are the biggest killer there.<br>
(and the law... errgh)<br>
So It would be nice if someone came along and "fixed" this for me, added all the client side encryption and decryption code. But for now it's just a fun project to look at.<br>
JUST DON'T, please don't, actually use it outside of a private network in this state.<br><br><br>

Fun fact: As the site is so small (235 KB compressed) you can actuly upload the site to itself.

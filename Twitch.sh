#! /bin/bash

 
INRES="1920x1080"                                            # input resolution
OUTRES="800x600"                                           # Output resolution
#OUTRES="1024x640"                                     # Output resolution
FPS="60"                                                    # target FPS
QUAL="medium"                                               # one of the many FFMPEG preset on (k)ubuntu found in /usr/share/ffmpeg
# If you have low bandwidth, put the qual preset on 'fast' (upload bandwidth)
# If you have medium bandwitch put it on normal to medium
 
# Write your key in a file named .twitch_key in your home directory
STREAM_KEY="live_xxxxxxx_xxxxxxxxxxxxxxxxxxxxxxxxx"
 
avconv \
    -f x11grab -s $INRES  -r "$FPS" -i :0.0 \
    -f alsa -ac 2 -i pulse  \
    -vcodec libx264 -s $OUTRES -preset $QUAL \
    -acodec libmp3lame -ar 44100 -threads 6 -qscale 3 -b 712000  -bufsize 9112k \
    -f flv "rtmp://live.justin.tv/app/$STREAM_KEY"

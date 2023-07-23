# -*- coding: utf-8 -*-

import subprocess
import sys
import signal
import os
import logging
import json
import time
import sleekxmpp
import socket
import ssl
import re
import urllib3
from datetime import datetime
from xml.dom import minidom

# Setup logging.
logging.basicConfig(level=logging.INFO, format='[%(asctime)s] ' + os.environ.get('APP_ENV') + '.%(levelname)s: %(message)s', filename='storage/logs/nwws-' + datetime.utcnow().strftime("%Y-%m-%d") + '.log')

logging.info('Starting NWWS-OI Python client by Jim Buitt <jbuitt@gmail.com>')

# Dump PID out to file
pid = os.getpid()
with open('./storage/logs/nwws.pid', 'w') as f:
    f.write(str(pid))

# Python versions before 3.0 do not use UTF-8 encoding
# by default. To ensure that Unicode is handled properly
# throughout SleekXMPP, we will set the default encoding
# ourselves to UTF-8.
if sys.version_info < (3, 0):
    reload(sys)
    sys.setdefaultencoding('utf8')
else:
    raw_input = input

# Prevent multiple running instances by opening a port
logging.info('Opening a local port to prevent duplicate executions..')
s = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
s.bind(('localhost', 1975))

def sigint_handler(signal, frame):
    print('Caught INT signal, exiting.', file=sys.stderr)
    logging.info('Caught INT signal, exiting.')
    os.remove('storage/logs/nwws.pid')
    file = open('/tmp/exit_nwws', 'w')
    file.close()
    sys.exit(0)

def sigterm_handler(signal, frame):
    print('Caught TERM signal, exiting.', file=sys.stderr)
    logging.info('Caught TERM signal, exiting.')
    os.remove('storage/logs/nwws.pid')
    file = open('/tmp/exit_nwws', 'w')
    file.close()
    sys.exit(0)

def sigusr1_handler(signal, frame):
    logging.info('Caught USR1 signal, updating log file.')
    filehandler = logging.FileHandler('storage/logs/nwws-' + datetime.utcnow().strftime("%Y-%m-%d") + '.log', 'a')
    formatter = logging.Formatter('%(levelname)-8s %(message)s')
    filehandler.setFormatter(formatter)
    log = logging.getLogger()  # root logger - Good to get it only once.
    for hdlr in log.handlers[:]:  # remove the existing file handlers
        if isinstance(hdlr,logging.FileHandler):
            log.removeHandler(hdlr)
    log.addHandler(filehandler)      # set the new handler
    # set the log level to INFO as the default is ERROR
    log.setLevel(logging.INFO)

signal.signal(signal.SIGINT, sigint_handler)
signal.signal(signal.SIGTERM, sigterm_handler)
signal.signal(signal.SIGUSR1, sigusr1_handler)

class MUCBot(sleekxmpp.ClientXMPP):

    """
    A simple SleekXMPP bot that will greets those
    who enter the room, and acknowledge any messages
    that mentions the bot's nickname.
    """

    def __init__(self, jid, password, room, nick):
        sleekxmpp.ClientXMPP.__init__(self, jid, password)

        self.room = room
        self.nick = nick

        # The session_start event will be triggered when
        # the bot establishes its connection with the server
        # and the XML streams are ready for use. We want to
        # listen for this event so that we we can initialize
        # our roster.
        self.add_event_handler("session_start", self.start)

        # The groupchat_message event is triggered whenever a message
        # stanza is received from any chat room. If you also also
        # register a handler for the 'message' event, MUC messages
        # will be processed by both handlers.
        self.add_event_handler("groupchat_message", self.muc_message)

        # The groupchat_presence event is triggered whenever a
        # presence stanza is received from any chat room, including
        # any presences you send yourself. To limit event handling
        # to a single room, use the events muc::room@server::presence,
        # muc::room@server::got_online, or muc::room@server::got_offline.
        self.add_event_handler("muc::%s::got_online" % self.room,
                               self.muc_online)

    def start(self, event):
        """
        Process the session_start event.

        Typical actions for the session_start event are
        requesting the roster and broadcasting an initial
        presence stanza.

        Arguments:
            event -- An empty dictionary. The session_start
                     event does not provide any additional
                     data.
        """
        self.get_roster()
        self.send_presence()
        self.plugin['xep_0045'].joinMUC(self.room,
                                        self.nick,
                                        # If a room password is needed, use:
                                        # password=the_room_password,
                                        wait=True)

    def muc_message(self, msg):
        """
        Process incoming message stanzas from any chat room. Be aware
        that if you also have any handlers for the 'message' event,
        message stanzas may be processed by both handlers, so check
        the 'type' attribute when using a 'message' event handler.

        Whenever the bot's nickname is mentioned, respond to
        the message.

        IMPORTANT: Always check that a message is not from yourself,
                   otherwise you will create an infinite loop responding
                   to your own messages.

        This handler will reply to messages that mention
        the bot's nickname.

        Arguments:
            msg -- The received message stanza. See the documentation
                   for stanza objects and the Message stanza to see
                   how it may be used.
        """
        #if msg['mucnick'] != self.nick and self.nick in msg['body']:
        #    self.send_message(mto=msg['from'].bare,
        #                      mbody="I heard that, %s." % msg['mucnick'],
        #                      mtype='groupchat')
        logging.info('Message stanza rcvd from nwws-oi saying... ' + msg['body'])
        xmldoc = minidom.parseString(str(msg))
        itemlist = xmldoc.getElementsByTagName('x')
        ttaaii = itemlist[0].attributes['ttaaii'].value.lower()
        cccc = itemlist[0].attributes['cccc'].value.lower()
        awipsid = itemlist[0].attributes['awipsid'].value.lower()
        id = itemlist[0].attributes['id'].value
        content = itemlist[0].firstChild.nodeValue
        file_written_flag = 0
        if awipsid:
            dayhourmin = datetime.utcnow().strftime("%d%H%M")
            filename = cccc + '_' + ttaaii + '-' + awipsid + '.' + dayhourmin + '_' + id + '.txt'
            # If user specified NWWSOI_FILE_SAVE_REGEX, check to see if filename matches supplied regex
            if not os.environ.get('NWWSOI_FILE_SAVE_REGEX') == None:
                if not re.fullmatch(os.environ.get('NWWSOI_FILE_SAVE_REGEX'), filename) == None:
                    logging.info("Writing " + filename)
                    if not os.path.exists(os.environ.get('NWWSOI_ARCHIVE_DIR') + '/' + cccc):
                        os.makedirs(os.environ.get('NWWSOI_ARCHIVE_DIR') + '/' + cccc)
                    # Remove every other line
                    lines = content.splitlines()
                    pathtofile = os.environ.get('NWWSOI_ARCHIVE_DIR') + '/' + cccc + '/' + filename
                    f = open(pathtofile, 'w')
                    count = 0
                    for line in lines:
                        if count == 0 and line == '':
                            continue
                        if count % 2 == 0:
                            f.write(line + "\n")
                        count += 1
                    f.close()
                    file_written_flag = 1
                else:
                    logging.info("Not writing " + filename + ", since it did not match NWWSOI_FILE_SAVE_REGEX.")
            else:
                logging.info("Writing " + filename)
                if not os.path.exists(os.environ.get('NWWSOI_ARCHIVE_DIR') + '/' + cccc):
                    os.makedirs(os.environ.get('NWWSOI_ARCHIVE_DIR') + '/' + cccc)
                # Remove every other line
                lines = content.splitlines()
                pathtofile = os.environ.get('NWWSOI_ARCHIVE_DIR') + '/' + cccc + '/' + filename
                f = open(pathtofile, 'w')
                count = 0
                for line in lines:
                    if count == 0 and line == '':
                        continue
                    if count % 2 == 0:
                        f.write(line + "\n")
                    count += 1
                f.close()
                file_written_flag = 1

            # If file was written out, check to see if a PAN_RUN script needs to be run
            if file_written_flag == 1:
                # Run a command using the file as the parameter (if pan_run is defined as an environment variable)
                if not os.environ.get('NWWSOI_PAN_RUN') == None and not os.environ.get('NWWSOI_PAN_RUN') == "":
                    try:
                        result = subprocess.run(os.environ.get('NWWSOI_PAN_RUN') + ' ' + pathtofile, stdout=subprocess.PIPE, stderr=subprocess.PIPE, shell=True, check=True)
                        logging.info('Successfully executed PAN_RUN command. Output:')
                        logging.info(' ' + result.stdout.decode('utf-8').strip('\n'))
                    except subprocess.CalledProcessError as e:
                        logging.error('Failed to execute PAN_RUN command:')
                        logging.error(' {}'.format(e.output).encode())

    def muc_online(self, presence):
        """
        Process a presence stanza from a chat room. In this case,
        presences from users that have just come online are
        handled by sending a welcome message that includes
        the user's nickname and role in the room.

        Arguments:
            presence -- The received presence stanza. See the
                        documentation for the Presence stanza
                        to see how else it may be used.
        """
        if presence['muc']['nick'] != self.nick:
            self.send_message(mto=presence['from'].bare,
                mbody="Hello, %s %s" % (presence['muc']['role'],
                    presence['muc']['nick']),
                mtype='groupchat')


if __name__ == '__main__':

    # Check for environment variables
    logging.info('Checking for environment variables..')
    envVars = [
        'NWWSOI_SERVER_HOST',
        'NWWSOI_SERVER_PORT',
        'NWWSOI_USERNAME',
        'NWWSOI_PASSWORD',
        'NWWSOI_RESOURCE',
        'NWWSOI_ARCHIVE_DIR',
        'NWWSOI_SERVER_CONNECT_RETRY',
    ]
    for envVar in envVars:
        if os.environ.get(envVar) == None:
            logging.error('The environment variable ' + envVar + ' does not exist, please set and try again.')
            sys.exit(1)

    # Create archive directory if it does not exist
    if not os.path.exists(os.environ.get('NWWSOI_ARCHIVE_DIR')):
        os.makedirs(os.environ.get('NWWSOI_ARCHIVE_DIR'))

    # Start endless loop
    logging.info('Starting execution loop..')
    while True:

        # Setup the MUCBot and register plugins. Note that while plugins may
        # have interdependencies, the order in which you register them does
        # not matter.
        logging.info('Setting up MUCBot..')
        xmpp = MUCBot(os.environ.get('NWWSOI_USERNAME') + '@'
            + os.environ.get('NWWSOI_SERVER_HOST'), os.environ.get('NWWSOI_PASSWORD'), 'nwws@conference.'
            + os.environ.get('NWWSOI_SERVER_HOST'), os.environ.get('NWWSOI_RESOURCE'))
        xmpp.register_plugin('xep_0030') # Service Discovery
        xmpp.register_plugin('xep_0045') # Multi-User Chat
        xmpp.register_plugin('xep_0199') # XMPP Ping

        # nwws-oi.weather.gov now requires TLSv2.3, will *not* connect without the following line
        xmpp.ssl_version = ssl.PROTOCOL_SSLv23

        # Connect to the XMPP server and start processing XMPP stanzas.
        logging.info('Connecting to XMPP server..')
        if xmpp.connect((os.environ.get('NWWSOI_SERVER_HOST'), os.environ.get('NWWSOI_SERVER_PORT')),
            os.environ.get('NWWSOI_SERVER_CONNECT_RETRY'),
            True,
            False
        ):
            # If you do not have the dnspython library installed, you will need
            # to manually specify the name of the server if it does not match
            # the one in the JID. For example, to use Google Talk you would
            # need to use:
            #
            # if xmpp.connect(('talk.google.com', 5222)):
            #     ...
            logging.info('Connected to XMPP server, starting to process incoming products.')
            xmpp.process(block=True)
            if os.path.isfile('/tmp/exit_nwws'):
                os.remove('/tmp/exit_nwws')
                logging.info('Exited.')
                sys.exit(0)
        else:
            logging.error('Unable to connect.')
            sys.exit(1)

        logging.info('Sleeping for 5 seconds.')
        time.sleep(5)

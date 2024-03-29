#! /usr/bin/env python

# This file belongs actually to the python-mpd project and is the same copyright (afaik)

# Edit these
HOST = "192.168.1.107"
PORT = 6600
PASS = None


from optparse import OptionParser
from socket import error as SocketError
from sys import stderr

from mpd import MPDClient, MPDError


ACTIONS = ("get", "set", "delete", "list", "find")


def main(action, uri, name, value):
    client = MPDClient()
    client.connect(HOST, PORT)
    if PASS:
        client.password(PASS)

    if action == "get":
        print client.sticker_get("song", uri, name)
    if action == "set":
        client.sticker_set("song", uri, name, value)
    if action == "delete":
        client.sticker_delete("song", uri, name)
    if action == "list":
        stickers = client.sticker_list("song", uri)
        for sticker in stickers:
            print sticker
    if action == "find":
        matches = client.sticker_find("song", uri, name)
        for match in matches:
            if "file" in match:
                print match["file"]


if __name__ == "__main__":
    parser = OptionParser(usage="%prog action args", version="0.1",
                          description="Manipulate and query "
                                      "MPD song stickers.")
    options, args = parser.parse_args()

    if len(args) < 1:
        parser.error("no action specified")
    action = args.pop(0)

    if action not in ACTIONS:
        parser.error("action must be one of: %s" % " ".join(ACTIONS))

    if len(args) < 1:
        parser.error("no URI specified")
    uri = args.pop(0)

    if action in ("get", "set", "delete", "find"):
        if len(args) < 1:
            parser.error("no name specified")
        name = args.pop(0)
    else:
        name = None

    if action == "set":
        if len(args) < 1:
            parser.error("no value specified")
        value = args.pop(0)
    else:
        value = None

    try:
        main(action, uri, name, value)
    except SocketError as e:
        print >> stderr, "%s: error with connection to MPD: %s" % \
                         (parser.get_prog_name(), e[1])
    except MPDError as e:
        print >> stderr, "%s: error executing action: %s" % \
                         (parser.get_prog_name(), e)


# vim: set expandtab shiftwidth=4 softtabstop=4 textwidth=79:
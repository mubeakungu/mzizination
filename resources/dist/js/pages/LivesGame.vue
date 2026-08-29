<template>
    <div class="container">
    <div class="slott"></div>
    <div id="back"><img src="/image/external.svg"></div>
    <div>
        <div class="game_slot">
            <div class="game-component">
                <div class="ingame-play__controls" style="width: 100%;">
                    <router-link to="/live" tag="div" class="ingame-play__control ingame-play__control_change">
                        <span>&lt;</span>
                    </router-link>
                    <div id="game_name" class="ingame-play__name ingame-play__control_change2">{{ live.name }}</div> &nbsp;
                    <div role="button" class="ingame-play__control " @click="iframeAction(4)"><img src="data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiPz4KPHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIiB3aWR0aD0iMTAwcHgiIGhlaWdodD0iMTAwcHgiIHZpZXdCb3g9IjAgMCAxMDAgMTAwIiB2ZXJzaW9uPSIxLjEiPgo8ZyBpZD0ic3VyZmFjZTEiPgo8cGF0aCBzdHlsZT0iIHN0cm9rZTpub25lO2ZpbGwtcnVsZTpub256ZXJvO2ZpbGw6cmdiKDEwMCUsMTAwJSwxMDAlKTtmaWxsLW9wYWNpdHk6MTsiIGQ9Ik0gNDkuOTUzMTI1IDguNzQyMTg4IEMgNDguNzQ2MDk0IDguNzQyMTg4IDQ3LjUzOTA2MiA5LjAxMTcxOSA0Ni42MDU0NjkgOS41NTQ2ODggTCAxNi43Njk1MzEgMjYuODEyNSBDIDE0LjkwMjM0NCAyNy44OTQ1MzEgMTQuOTAyMzQ0IDI5LjYwMTU2MiAxNi43Njk1MzEgMzAuNjgzNTk0IEwgNDYuNjA1NDY5IDQ3Ljk0MTQwNiBDIDQ4LjQ3NjU2MiA0OS4wMjM0MzggNTEuNDI5Njg4IDQ5LjAyMzQzOCA1My4zMDA3ODEgNDcuOTQxNDA2IEwgODMuMTMyODEyIDMwLjY4MzU5NCBDIDg1LjAwMzkwNiAyOS42MDE1NjIgODUuMDAzOTA2IDI3Ljg5NDUzMSA4My4xMzI4MTIgMjYuODEyNSBMIDUzLjMwMDc4MSA5LjU1NDY4OCBDIDUyLjM2MzI4MSA5LjAxMTcxOSA1MS4xNjAxNTYgOC43NDIxODggNDkuOTUzMTI1IDguNzQyMTg4IFogTSA0OS43OTI5NjkgMTkuMTE3MTg4IEMgNTEuNTUwNzgxIDE5LjEzNjcxOSA1My4xMzY3MTkgMTkuNTM5MDYyIDU0LjUzOTA2MiAyMC4zMjQyMTkgQyA1NS41IDIwLjg2MzI4MSA1Ni4xMjg5MDYgMjEuNDc2NTYyIDU2LjQyOTY4OCAyMi4xNjc5NjkgQyA1Ni43MTQ4NDQgMjIuODU1NDY5IDU2LjczNDM3NSAyMy43NTc4MTIgNTYuNDgwNDY5IDI0Ljg3NSBMIDU2LjE2Nzk2OSAyNS45OTIxODggQyA1NS45NjA5MzggMjYuNzg5MDYyIDU1LjkxNzk2OSAyNy4zNjMyODEgNTYuMDM5MDYyIDI3LjcxMDkzOCBDIDU2LjE0ODQzOCAyOC4wNTQ2ODggNTYuNDI1NzgxIDI4LjM1MTU2MiA1Ni44NjcxODggMjguNTk3NjU2IEwgNTcuNTI3MzQ0IDI4Ljk2ODc1IEwgNTEuMDM1MTU2IDMyLjYwNTQ2OSBMIDUwLjMxNjQwNiAzMi4yMDMxMjUgQyA0OS41MTE3MTkgMzEuNzUzOTA2IDQ5IDMxLjI0MjE4OCA0OC43NzczNDQgMzAuNjc1NzgxIEMgNDguNTQyOTY5IDMwLjEwMTU2MiA0OC41ODIwMzEgMjkuMTkxNDA2IDQ4Ljg5MDYyNSAyNy45NDE0MDYgTCA0OS4xOTE0MDYgMjYuODE2NDA2IEMgNDkuMzYzMjgxIDI2LjE0ODQzOCA0OS4zNzg5MDYgMjUuNjAxNTYyIDQ5LjI0MjE4OCAyNS4xNzE4NzUgQyA0OS4xMTMyODEgMjQuNzM4MjgxIDQ4LjgyNDIxOSAyNC4zOTQ1MzEgNDguMzcxMDk0IDI0LjE0NDUzMSBDIDQ3LjY4NzUgMjMuNzU3ODEyIDQ2Ljg2MzI4MSAyMy42MjEwOTQgNDUuOTA2MjUgMjMuNzI2NTYyIEMgNDQuOTM3NSAyMy44MzIwMzEgNDMuOTM3NSAyNC4xNzE4NzUgNDIuOTA2MjUgMjQuNzUgQyA0MS45Mzc1IDI1LjI5Mjk2OSA0MS4wNTQ2ODggMjUuOTcyNjU2IDQwLjI2MTcxOSAyNi43OTI5NjkgQyAzOS40NTcwMzEgMjcuNjA1NDY5IDM4Ljc2OTUzMSAyOC41MzUxNTYgMzguMTkxNDA2IDI5LjU4MjAzMSBMIDMzLjU3NDIxOSAyNi45OTIxODggQyAzNC42MTMyODEgMjUuOTQ5MjE5IDM1LjYzMjgxMiAyNS4wMzkwNjIgMzYuNjM2NzE5IDI0LjI1NzgxMiBDIDM3LjYzNjcxOSAyMy40NzY1NjIgMzguNjk1MzEyIDIyLjc3MzQzOCAzOS44MTI1IDIyLjE0ODQzOCBDIDQyLjczODI4MSAyMC41MDc4MTIgNDUuNDU3MDMxIDE5LjUzNTE1NiA0Ny45NzI2NTYgMTkuMjIyNjU2IEMgNDguNTk3NjU2IDE5LjE0NDUzMSA0OS4yMDcwMzEgMTkuMTA5Mzc1IDQ5Ljc5Mjk2OSAxOS4xMTcxODggWiBNIDU5LjcwNzAzMSAzMC4xOTE0MDYgTCA2NC45NDE0MDYgMzMuMTI1IEwgNTguNDUzMTI1IDM2Ljc2MTcxOSBMIDUzLjIxNDg0NCAzMy44MjgxMjUgWiBNIDE0Ljc3NzM0NCAzMy45NTMxMjUgQyAxMy42NTYyNSAzMy45MjE4NzUgMTIuODkwNjI1IDM0LjgwMDc4MSAxMi44OTA2MjUgMzYuMzUxNTYyIEwgMTIuODkwNjI1IDY3LjE1MjM0NCBDIDEyLjg5MDYyNSA2OS4zMDg1OTQgMTQuMzcxMDk0IDcxLjg3MTA5NCAxNi4yMzgyODEgNzIuOTQ5MjE5IEwgNDQuOTM3NSA4OS41MjM0MzggQyA0Ni44MDQ2ODggOTAuNjAxNTYyIDQ4LjI4NTE1NiA4OS43NSA0OC4yODUxNTYgODcuNTg5ODQ0IEwgNDguMjg1MTU2IDU2Ljc4OTA2MiBDIDQ4LjI4NTE1NiA1NC42Mjg5MDYgNDYuODA0Njg4IDUyLjA3MDMxMiA0NC45Mzc1IDUwLjk5MjE4OCBMIDE2LjIzODI4MSAzNC40MTc5NjkgQyAxNS43MTQ4NDQgMzQuMTE3MTg4IDE1LjIxODc1IDMzLjk2NDg0NCAxNC43ODEyNSAzMy45NTMxMjUgWiBNIDg1LjI0MjE4OCAzMy45NTMxMjUgQyA4NC44MDQ2ODggMzMuOTY0ODQ0IDg0LjMwODU5NCAzNC4xMTcxODggODMuNzgxMjUgMzQuNDE3OTY5IEwgNTUuMDg1OTM4IDUwLjk5MjE4OCBDIDUzLjIxNDg0NCA1Mi4wNzAzMTIgNTEuNzM4MjgxIDU0LjYzMjgxMiA1MS43MzgyODEgNTYuNzg5MDYyIEwgNTEuNzM4MjgxIDg3LjU4OTg0NCBDIDUxLjczODI4MSA4OS43NSA1My4yMTQ4NDQgOTAuNjAxNTYyIDU1LjA4NTkzOCA4OS41MjM0MzggTCA4My43ODEyNSA3Mi45NTMxMjUgQyA4NS42NTIzNDQgNzEuODcxMDk0IDg3LjEyODkwNiA2OS4zMTI1IDg3LjEyODkwNiA2Ny4xNTIzNDQgTCA4Ny4xMjg5MDYgMzYuMzUxNTYyIEMgODcuMTI4OTA2IDM0LjgwMDc4MSA4Ni4zNjcxODggMzMuOTI1NzgxIDg1LjI0MjE4OCAzMy45NTMxMjUgWiBNIDIyLjQ0MTQwNiA0Ni40MDYyNSBDIDIzLjcyMjY1NiA0Ni42NzE4NzUgMjQuODkwNjI1IDQ3IDI1Ljk0OTIxOSA0Ny4zODI4MTIgQyAyNy4wMDM5MDYgNDcuNzY5NTMxIDI4LjAyNzM0NCA0OC4yNDYwOTQgMjkuMDExNzE5IDQ4LjgxNjQwNiBDIDMxLjU4OTg0NCA1MC4zMDQ2ODggMzMuNTU4NTk0IDUyLjAwMzkwNiAzNC45MTAxNTYgNTMuOTEwMTU2IEMgMzYuMjY1NjI1IDU1LjgwNDY4OCAzNi45NDE0MDYgNTcuODI0MjE5IDM2Ljk0MTQwNiA1OS45NzI2NTYgQyAzNi45NDE0MDYgNjEuMDc0MjE5IDM2LjczODI4MSA2MS45NDE0MDYgMzYuMzI0MjE5IDYyLjU4MjAzMSBDIDM1LjkxMDE1NiA2My4yMTA5MzggMzUuMjA3MDMxIDYzLjczNDM3NSAzNC4yMTQ4NDQgNjQuMTY0MDYyIEwgMzMuMTk5MjE5IDY0LjUzOTA2MiBDIDMyLjQ4MDQ2OSA2NC44MjAzMTIgMzIuMDA3ODEyIDY1LjEwOTM3NSAzMS43ODUxNTYgNjUuNDEwMTU2IEMgMzEuNTYyNSA2NS42OTUzMTIgMzEuNDUzMTI1IDY2LjA5Mzc1IDMxLjQ1MzEyNSA2Ni41OTc2NTYgTCAzMS40NTMxMjUgNjcuMzU1NDY5IEwgMjUuNzI2NTYyIDY0LjA1MDc4MSBMIDI1LjcyNjU2MiA2My4yMjI2NTYgQyAyNS43MjY1NjIgNjIuMzAwNzgxIDI1Ljg5ODQzOCA2MS41ODU5MzggMjYuMjUgNjEuMDgyMDMxIEMgMjYuNTk3NjU2IDYwLjU2MjUgMjcuMzMyMDMxIDYwLjA3ODEyNSAyOC40NTMxMjUgNTkuNjIxMDk0IEwgMjkuNDY4NzUgNTkuMjMwNDY5IEMgMzAuMDc0MjE5IDU4Ljk5NjA5NCAzMC41MTE3MTkgNTguNjk5MjE5IDMwLjc4NTE1NiA1OC4zMzk4NDQgQyAzMS4wNzAzMTIgNTcuOTg4MjgxIDMxLjIxNDg0NCA1Ny41NTA3ODEgMzEuMjE0ODQ0IDU3LjAzNTE1NiBDIDMxLjIxNDg0NCA1Ni4yNSAzMC45NjA5MzggNTUuNDg4MjgxIDMwLjQ1MzEyNSA1NC43NTc4MTIgQyAyOS45NDUzMTIgNTQuMDE1NjI1IDI5LjIzODI4MSA1My4zNzg5MDYgMjguMzI4MTI1IDUyLjg1NTQ2OSBDIDI3LjQ2ODc1IDUyLjM1OTM3NSAyNi41NDY4NzUgNTIuMDE1NjI1IDI1LjU1MDc4MSA1MS44MjQyMTkgQyAyNC41NTQ2ODggNTEuNjIxMDk0IDIzLjUxOTUzMSA1MS41ODIwMzEgMjIuNDQxNDA2IDUxLjY5OTIxOSBaIE0gNzUuMDg1OTM4IDQ4LjA4NTkzOCBDIDc1LjM4MjgxMiA0OC4wODU5MzggNzUuNjU2MjUgNDguMTE3MTg4IDc1LjkxNDA2MiA0OC4xODM1OTQgQyA3Ny4yNjU2MjUgNDguNTE1NjI1IDc3Ljk0MTQwNiA0OS43NTM5MDYgNzcuOTQxNDA2IDUxLjg5ODQzOCBDIDc3Ljk0MTQwNiA1MyA3Ny43MzgyODEgNTQuMTA5Mzc1IDc3LjMyNDIxOSA1NS4yMjY1NjIgQyA3Ni45MTAxNTYgNTYuMzI4MTI1IDc2LjIwNzAzMSA1Ny42Njc5NjkgNzUuMjE0ODQ0IDU5LjI0MjE4OCBMIDc0LjE5OTIxOSA2MC43ODkwNjIgQyA3My40ODA0NjkgNjEuOTAyMzQ0IDczLjAwNzgxMiA2Mi43MzQzNzUgNzIuNzg1MTU2IDYzLjI4OTA2MiBDIDcyLjU2MjUgNjMuODM1OTM4IDcyLjQ1MzEyNSA2NC4zNTkzNzUgNzIuNDUzMTI1IDY0Ljg2NzE4OCBMIDcyLjQ1MzEyNSA2NS42MjUgTCA2Ni43MjY1NjIgNjguOTI5Njg4IEwgNjYuNzI2NTYyIDY4LjEwNTQ2OSBDIDY2LjcyNjU2MiA2Ny4xODM1OTQgNjYuOTAyMzQ0IDY2LjI2NTYyNSA2Ny4yNSA2NS4zNTkzNzUgQyA2Ny41OTc2NTYgNjQuNDM3NSA2OC4zMzIwMzEgNjMuMTAxNTYyIDY5LjQ1MzEyNSA2MS4zNTE1NjIgTCA3MC40Njg3NSA1OS43ODkwNjIgQyA3MS4wNzQyMTkgNTguODU1NDY5IDcxLjUxMTcxOSA1OC4wNTA3ODEgNzEuNzg5MDYyIDU3LjM3NSBDIDcyLjA3NDIxOSA1Ni42OTUzMTIgNzIuMjE0ODQ0IDU2LjA5Mzc1IDcyLjIxNDg0NCA1NS41NzgxMjUgQyA3Mi4yMTQ4NDQgNTQuNzg5MDYyIDcxLjk2MDkzOCA1NC4zMjQyMTkgNzEuNDUzMTI1IDU0LjE3OTY4OCBDIDcwLjk0NTMxMiA1NC4wMjM0MzggNzAuMjM4MjgxIDU0LjIwNzAzMSA2OS4zMjgxMjUgNTQuNzMwNDY5IEMgNjguNDcyNjU2IDU1LjIyNjU2MiA2Ny41NDY4NzUgNTUuOTUzMTI1IDY2LjU1MDc4MSA1Ni45MTAxNTYgQyA2NS41NTg1OTQgNTcuODU1NDY5IDY0LjUxOTUzMSA1OS4wMDc4MTIgNjMuNDQxNDA2IDYwLjM3NSBMIDYzLjQ0MTQwNiA1NS4wNzgxMjUgQyA2NC43MjI2NTYgNTMuODY3MTg4IDY1Ljg5MDYyNSA1Mi44NDM3NSA2Ni45NDkyMTkgNTIuMDA3ODEyIEMgNjguMDAzOTA2IDUxLjE3MTg3NSA2OS4wMjczNDQgNTAuNDcyNjU2IDcwLjAxMTcxOSA0OS45MDIzNDQgQyA3Mi4xMDU0NjkgNDguNjkxNDA2IDczLjc5Njg3NSA0OC4wODU5MzggNzUuMDg1OTM4IDQ4LjA4NTkzOCBaIE0gMjUuNzI2NTYyIDY2LjU0Njg3NSBMIDMxLjQ1MzEyNSA2OS44NTE1NjIgTCAzMS40NTMxMjUgNzUuODU1NDY5IEwgMjUuNzI2NTYyIDcyLjU1MDc4MSBaIE0gNzIuNDUzMTI1IDY4LjEyMTA5NCBMIDcyLjQ1MzEyNSA3NC4xMjUgTCA2Ni43MjY1NjIgNzcuNDI5Njg4IEwgNjYuNzI2NTYyIDcxLjQyNTc4MSBaIE0gNzIuNDUzMTI1IDY4LjEyMTA5NCAiLz4KPC9nPgo8L3N2Zz4K" class="random_dice"></div>
                    <div role="button" class="ingame-play__control" @click="fullscreen()"><img src="data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiPz4KPHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIiB3aWR0aD0iMTAwcHgiIGhlaWdodD0iMTAwcHgiIHZpZXdCb3g9IjAgMCAxMDAgMTAwIiB2ZXJzaW9uPSIxLjEiPgo8ZyBpZD0ic3VyZmFjZTEiPgo8cGF0aCBzdHlsZT0iIHN0cm9rZTpub25lO2ZpbGwtcnVsZTpub256ZXJvO2ZpbGw6cmdiKDEwMCUsMTAwJSwxMDAlKTtmaWxsLW9wYWNpdHk6MTsiIGQ9Ik0gOTUuNDUzMTI1IDYzLjYzNjcxOSBDIDkyLjk0NTMxMiA2My42MzY3MTkgOTAuOTEwMTU2IDY1LjY3MTg3NSA5MC45MTAxNTYgNjguMTgzNTk0IEwgOTAuOTEwMTU2IDg0LjQ4MDQ2OSBMIDYyLjMwNDY4OCA1NS44NzUgQyA2MC41MzEyNSA1NC4xMDE1NjIgNTcuNjUyMzQ0IDU0LjEwMTU2MiA1NS44Nzg5MDYgNTUuODc1IEMgNTQuMTAxNTYyIDU3LjY1MjM0NCA1NC4xMDE1NjIgNjAuNTMxMjUgNTUuODc4OTA2IDYyLjMwNDY4OCBMIDg0LjQ4MDQ2OSA5MC45MDYyNSBMIDY4LjE3OTY4OCA5MC45MTAxNTYgQyA2NS42NzE4NzUgOTAuOTEwMTU2IDYzLjYzNjcxOSA5Mi45NDUzMTIgNjMuNjM2NzE5IDk1LjQ1MzEyNSBDIDYzLjYzNjcxOSA5Ny45NjQ4NDQgNjUuNjcxODc1IDEwMCA2OC4xODM1OTQgMTAwIEwgOTUuNDUzMTI1IDEwMCBDIDk3Ljk2NDg0NCAxMDAgMTAwIDk3Ljk2NDg0NCAxMDAgOTUuNDUzMTI1IEwgMTAwIDY4LjE4MzU5NCBDIDEwMCA2NS42NzE4NzUgOTcuOTY0ODQ0IDYzLjYzNjcxOSA5NS40NTMxMjUgNjMuNjM2NzE5IFogTSA5NS40NTMxMjUgNjMuNjM2NzE5ICIvPgo8cGF0aCBzdHlsZT0iIHN0cm9rZTpub25lO2ZpbGwtcnVsZTpub256ZXJvO2ZpbGw6cmdiKDEwMCUsMTAwJSwxMDAlKTtmaWxsLW9wYWNpdHk6MTsiIGQ9Ik0gNC41NDY4NzUgMzYuMzYzMjgxIEMgNy4wNTQ2ODggMzYuMzYzMjgxIDkuMDg5ODQ0IDM0LjMyODEyNSA5LjA4OTg0NCAzMS44MTY0MDYgTCA5LjA4OTg0NCAxNS41MTk1MzEgTCAzNy42OTUzMTIgNDQuMTI1IEMgMzguNTgyMDMxIDQ1LjAxMTcxOSAzOS43NDYwOTQgNDUuNDUzMTI1IDQwLjkxMDE1NiA0NS40NTMxMjUgQyA0Mi4wNzAzMTIgNDUuNDUzMTI1IDQzLjIzNDM3NSA0NS4wMTE3MTkgNDQuMTIxMDk0IDQ0LjEyNSBDIDQ1Ljg5ODQzOCA0Mi4zNDc2NTYgNDUuODk4NDM4IDM5LjQ2ODc1IDQ0LjEyMTA5NCAzNy42OTUzMTIgTCAxNS41MTk1MzEgOS4wOTM3NSBMIDMxLjgyMDMxMiA5LjA4OTg0NCBDIDM0LjMyODEyNSA5LjA4OTg0NCAzNi4zNjMyODEgNy4wNTQ2ODggMzYuMzYzMjgxIDQuNTQ2ODc1IEMgMzYuMzYzMjgxIDIuMDM1MTU2IDM0LjMyODEyNSAwIDMxLjgxNjQwNiAwIEwgNC41NDY4NzUgMCBDIDIuMDM1MTU2IDAgMCAyLjAzNTE1NiAwIDQuNTQ2ODc1IEwgMCAzMS44MTY0MDYgQyAwIDM0LjMyODEyNSAyLjAzNTE1NiAzNi4zNjMyODEgNC41NDY4NzUgMzYuMzYzMjgxIFogTSA0LjU0Njg3NSAzNi4zNjMyODEgIi8+CjxwYXRoIHN0eWxlPSIgc3Ryb2tlOm5vbmU7ZmlsbC1ydWxlOm5vbnplcm87ZmlsbDpyZ2IoMTAwJSwxMDAlLDEwMCUpO2ZpbGwtb3BhY2l0eToxOyIgZD0iTSAzNy42OTUzMTIgNTUuODc4OTA2IEwgOS4wOTM3NSA4NC40ODA0NjkgTCA5LjA4OTg0NCA2OC4xNzk2ODggQyA5LjA4OTg0NCA2NS42NzE4NzUgNy4wNTQ2ODggNjMuNjM2NzE5IDQuNTQ2ODc1IDYzLjYzNjcxOSBDIDIuMDM1MTU2IDYzLjYzNjcxOSAwIDY1LjY3MTg3NSAwIDY4LjE4MzU5NCBMIDAgOTUuNDUzMTI1IEMgMCA5Ny45NjQ4NDQgMi4wMzUxNTYgMTAwIDQuNTQ2ODc1IDEwMCBMIDMxLjgxNjQwNiAxMDAgQyAzNC4zMjgxMjUgMTAwIDM2LjM2MzI4MSA5Ny45NjQ4NDQgMzYuMzYzMjgxIDk1LjQ1MzEyNSBDIDM2LjM2MzI4MSA5Mi45NDUzMTIgMzQuMzI4MTI1IDkwLjkxMDE1NiAzMS44MTY0MDYgOTAuOTEwMTU2IEwgMTUuNTE5NTMxIDkwLjkxMDE1NiBMIDQ0LjEyNSA2Mi4zMDQ2ODggQyA0NS44OTg0MzggNjAuNTMxMjUgNDUuODk4NDM4IDU3LjY1MjM0NCA0NC4xMjUgNTUuODc4OTA2IEMgNDIuMzQ3NjU2IDU0LjEwMTU2MiAzOS40Njg3NSA1NC4xMDE1NjIgMzcuNjk1MzEyIDU1Ljg3ODkwNiBaIE0gMzcuNjk1MzEyIDU1Ljg3ODkwNiAiLz4KPHBhdGggc3R5bGU9IiBzdHJva2U6bm9uZTtmaWxsLXJ1bGU6bm9uemVybztmaWxsOnJnYigxMDAlLDEwMCUsMTAwJSk7ZmlsbC1vcGFjaXR5OjE7IiBkPSJNIDU5LjA4OTg0NCA0NS40NTMxMjUgQyA2MC4yNTM5MDYgNDUuNDUzMTI1IDYxLjQxNzk2OSA0NS4wMTE3MTkgNjIuMzA0Njg4IDQ0LjEyMTA5NCBMIDkwLjkwNjI1IDE1LjUxOTUzMSBMIDkwLjkxMDE1NiAzMS44MjAzMTIgQyA5MC45MTAxNTYgMzQuMzI4MTI1IDkyLjk0NTMxMiAzNi4zNjMyODEgOTUuNDUzMTI1IDM2LjM2MzI4MSBDIDk3Ljk2NDg0NCAzNi4zNjMyODEgMTAwIDM0LjMyODEyNSAxMDAgMzEuODE2NDA2IEwgMTAwIDQuNTQ2ODc1IEMgOTkuOTk2MDk0IDIuMDM1MTU2IDk3Ljk2NDg0NCAwIDk1LjQ1MzEyNSAwIEwgNjguMTgzNTk0IDAgQyA2NS42NzE4NzUgMCA2My42MzY3MTkgMi4wMzUxNTYgNjMuNjM2NzE5IDQuNTQ2ODc1IEMgNjMuNjM2NzE5IDcuMDU0Njg4IDY1LjY3MTg3NSA5LjA4OTg0NCA2OC4xODM1OTQgOS4wODk4NDQgTCA4NC40ODA0NjkgOS4wODk4NDQgTCA1NS44NzUgMzcuNjk1MzEyIEMgNTQuMTAxNTYyIDM5LjQ2ODc1IDU0LjEwMTU2MiA0Mi4zNDc2NTYgNTUuODc1IDQ0LjEyMTA5NCBDIDU2Ljc2NTYyNSA0NS4wMTE3MTkgNTcuOTI1NzgxIDQ1LjQ1MzEyNSA1OS4wODk4NDQgNDUuNDUzMTI1IFogTSA1OS4wODk4NDQgNDUuNDUzMTI1ICIvPgo8L2c+Cjwvc3ZnPgo="></div>
                    <div class="ingame-play__control" @click="iframeAction(3)"><img src="data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiPz4KPHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIiB3aWR0aD0iMTAwcHgiIGhlaWdodD0iMTAwcHgiIHZpZXdCb3g9IjAgMCAxMDAgMTAwIiB2ZXJzaW9uPSIxLjEiPgo8ZyBpZD0ic3VyZmFjZTEiPgo8cGF0aCBzdHlsZT0iIHN0cm9rZTpub25lO2ZpbGwtcnVsZTpub256ZXJvO2ZpbGw6cmdiKDEwMCUsMTAwJSwxMDAlKTtmaWxsLW9wYWNpdHk6MTsiIGQ9Ik0gOTMuODM1OTM4IDAgTCA1OS40NTMxMjUgMCBDIDU2LjA1NDY4OCAwIDUzLjI4OTA2MiAyLjc2NTYyNSA1My4yODkwNjIgNi4xNjQwNjIgQyA1My4yODkwNjIgOS41NjI1IDU2LjA1NDY4OCAxMi4zMjgxMjUgNTkuNDUzMTI1IDEyLjMyODEyNSBMIDc4Ljk1NzAzMSAxMi4zMjgxMjUgTCA0Ni40MjE4NzUgNDQuODYzMjgxIEMgNDUuMjU3ODEyIDQ2LjAyNzM0NCA0NC42MTMyODEgNDcuNTc0MjE5IDQ0LjYxMzI4MSA0OS4yMjI2NTYgQyA0NC42MTMyODEgNTAuODY3MTg4IDQ1LjI1MzkwNiA1Mi40MTQwNjIgNDYuNDIxODc1IDUzLjU3ODEyNSBDIDQ3LjU4NTkzOCA1NC43NDIxODggNDkuMTMyODEyIDU1LjM4NjcxOSA1MC43NzczNDQgNTUuMzg2NzE5IEMgNTIuNDI1NzgxIDU1LjM4NjcxOSA1My45NzI2NTYgNTQuNzQyMTg4IDU1LjEzNjcxOSA1My41NzgxMjUgTCA4Ny42NzE4NzUgMjEuMDQyOTY5IEwgODcuNjcxODc1IDQwLjU0Njg3NSBDIDg3LjY3MTg3NSA0My45NDUzMTIgOTAuNDM3NSA0Ni43MTA5MzggOTMuODM1OTM4IDQ2LjcxMDkzOCBDIDk3LjIzNDM3NSA0Ni43MTA5MzggMTAwIDQzLjk0NTMxMiAxMDAgNDAuNTQ2ODc1IEwgMTAwIDYuMTY0MDYyIEMgMTAwIDIuNzY1NjI1IDk3LjIzNDM3NSAwIDkzLjgzNTkzOCAwIFogTSA5My44MzU5MzggMCAiLz4KPHBhdGggc3R5bGU9IiBzdHJva2U6bm9uZTtmaWxsLXJ1bGU6bm9uemVybztmaWxsOnJnYigxMDAlLDEwMCUsMTAwJSk7ZmlsbC1vcGFjaXR5OjE7IiBkPSJNIDcwLjg0Mzc1IDg5LjQzMzU5NCBMIDEwLjU2NjQwNiA4OS40MzM1OTQgTCAxMC41NjY0MDYgMjkuMTU2MjUgTCA1NS44OTg0MzggMjkuMTU2MjUgTCA2Ni40NjQ4NDQgMTguNTg5ODQ0IEwgNS4yODEyNSAxOC41ODk4NDQgQyAyLjM2NzE4OCAxOC41ODk4NDQgMCAyMC45NTcwMzEgMCAyMy44NzUgTCAwIDk0LjcxODc1IEMgMCA5Ny42MzI4MTIgMi4zNjcxODggMTAwIDUuMjgxMjUgMTAwIEwgNzYuMTI1IDEwMCBDIDc5LjA0Mjk2OSAxMDAgODEuNDEwMTU2IDk3LjYzMjgxMiA4MS40MTAxNTYgOTQuNzE4NzUgTCA4MS40MTAxNTYgMzMuNTM1MTU2IEwgNzAuODQzNzUgNDQuMTAxNTYyIFogTSA3MC44NDM3NSA4OS40MzM1OTQgIi8+CjwvZz4KPC9zdmc+Cg=="></div>
                </div>
                <div class="ingame-play__wrapper-place">
                    <iframe
                        id="iframe_slot"
                        scrolling="no"
                        frameborder="0"
                        webkitallowfullscreen="true"
                        allowfullscreen="allowfullscreen"
                        mozallowfullscreen="true"
                        :src="live.link"
                    ></iframe>
                </div>
            </div>
        </div>
    </div>
</div>
</template>
<script>
    export default {
            data() {
                return {
                    live: {
                        name: null,
                        link: null
                    }
                }
            },
            mounted() {
                this.getSlot(this.$route.params.id);
            },
            methods: {
                iframeAction(type) {
                    if(type == 3) {
                        window.open(this.slot.link);
                    } else if(type == 4) {
                        this.$root.axios.post('/lives/getRandom').then((res) => {
                            if(res.data.error) {
                                this.$root.$emit('noty', {
                                    title: 'Ошибка',
                                    type: 'error',
                                    text: res.data.msg
                                })
                                return this.$router.push('/live')
                            }

                            this.$router.push('/live/game/'+ res.data.id);
                            this.getSlot(res.data.id);
                        })
                    }
                },
                getSlot(id) {
                    this.$root.axios.post('/lives/get/'+ id).then((res) => {
                        if(res.data.error) {
                            this.$root.$emit('noty', {
                                title: 'Ошибка',
                                type: 'error',
                                text: res.data.msg
                            })
                            return this.$router.push('/live')
                        }
                       this.live = res.data;
                    });
                },
                fullscreen() {
                    var docElm = document.getElementById('iframe_slot');  
    
                    if (docElm.requestFullscreen) {  
                        docElm.requestFullscreen();  
                    }  
                    else if (docElm.mozRequestFullScreen) {  
                        docElm.mozRequestFullScreen();  
                    }  
                    else if (docElm.webkitRequestFullScreen) {  
                        docElm.webkitRequestFullScreen();  
                    }  
                    else if (docElm.webkitFullscreenElement) {
                        docElm.webkitFullscreenElement();
                    }
                    else if (docElm.msFullscreenElement) {
                        docElm.msFullscreenElement();
                    }
                    else if (docElm.msRequestFullscreen) {  
                        docElm.msRequestFullscreen();  
                    } else {
                        docElm.documentElement;
                        docElm.webkitRequestFullscreen(Element.ALLOW_KEYBOARD_INPUT);
                    }  
    
                }
            },
            sockets: {
                updateBalance(data) {
                    if(data.user_id == this.$root.user.ref_code) return $('#balance').html(data.balance.toFixed(2));   
                }
            }
        }
    </script>
<style scoped>
body {
    background-color: transperent!important;
    background-image: none!important;
}

.ingame-play__control.active {
    background-image: linear-gradient(45deg,#6825d0,#ff1e3f);
}

.slott,.slott img {
    display: block;
    height: 100%;
    left: 0;
    position: absolute;
    top: 0;
    width: 100%;
    z-index: -1;
}

.slott:before {
    background: linear-gradient(180deg,rgba(20,27,52,0),rgba(20,27,52,.8));
    height: 200px;
    left: 0;
    top: 0;
    transform: matrix(1,0,0,-1,0,0);
    z-index: 1;
}

.slott:after,.slott:before {
    content: "";
    display: block;
    position: absolute;
    width: 100%;
}

.slott img {
    border: none;
    -o-object-fit: cover;
    object-fit: cover;
    -o-object-position: center;
    object-position: center;
    opacity: .9;
    z-index: -1;
}

.slott:after {
    background: linear-gradient(180deg,rgba(20,27,52,0),rgba(20,27,52,.6));
    bottom: 0;
    height: 200px;
    left: 50%;
    transform: translateX(-50%);
}

#back {
    align-items: center;
    background-image: linear-gradient(45deg,#2561d0,#1e4eff);
    border-radius: 5px;
    cursor: pointer;
    display: none;
    height: 36px;
    justify-content: center;
    left: 0;
    min-width: 36px;
    opacity: .7;
    position: absolute;
    top: 51px;
    width: 36px;
    z-index: 9999;
}

#back>img {
    height: 18px;
    margin: 10px;
    transform: rotate(45deg);
    transition: filter .2s ease-in-out;
    width: 18px;
}

@media (max-width:420px) {
    .ingame-play__control_change {
        display: none!important;
    }

    .game_slot {
        top: 10px!important;
    }

    .ingame-play__controls {
        margin-bottom: 10px!important;
    }

    .ingame-play__name {
        display: block!important;
        margin-left: 4px;
    }
}

@media (min-width:1000px) {
    .container.slots {
        margin-right: 200px;
        max-width: 1400px!important;
    }
}

@media (max-width:1000px) {
    .ingame-play__name {
        font-size: 14px!important;
        letter-spacing: 1px!important;
    }
}

img.random_dice {
    height: 26px!important;
    width: 26px!important;
}

.game_slot {
    display: flex;
}

.game-component,.game_slot {
    align-items: stretch;
    position: relative;
    width: 100%;
}

.ingame-play__wrapper-place {
    background: #1b2341;
    border-bottom-left-radius: 10px;
    border-bottom-right-radius: 10px;
    box-shadow: 0 0 10px rgb(30 30 30/50%);
    padding-bottom: 10px;
    padding-top: 56.5%;
    position: relative;
    width: 100%;
}

.ingame-play__wrapper-place:before {
    -webkit-animation: pulse 2.5s infinite;
    animation: pulse 2.5s infinite;
    background-image: url(/image/mob_logo_d.png);
    background-position: top;
    background-repeat: no-repeat;
    background-size: contain;
    content: "";
    display: block;
    height: 60px;
    margin: 0 auto;
    position: absolute;
    top: 0;
    top: 40%;
    width: 100%;
}

@-webkit-keyframes pulse {
    50% {
        transform: scale(1.15);
    }
}

@keyframes pulse {
    50% {
        transform: scale(1.15);
    }
}

.ingame-play__wrapper-place iframe {
    border-bottom-left-radius: 10px;
    border-bottom-right-radius: 10px;
    height: 100%;
    left: 0;
    overflow: hidden;
    position: absolute;
    top: 0;
    width: 100%;
}

.ingame-play__control:hover {
    background-image: linear-gradient(45deg,#4d84ea,#486ef7);
}

.mt-5,.my-5 {
    margin-top: 0!important;
}

.ingame-play__controls {
    align-items: center;
    background-image: linear-gradient(180deg,rgb(75 75 75/20%),rgb(75 75 75/50%));
    border-top-left-radius: 10px;
    border-top-right-radius: 10px;
    display: flex;
    padding: 6px;
    position: relative;
    right: 0;
    -webkit-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
    user-select: none;
}

.ingame-play__control {
    align-items: center;
    border-radius: 5px;
    cursor: pointer;
    display: flex;
    height: 36px;
    justify-content: center;
    min-width: 36px;
    width: 36px;
}

.ingame-play__control span {
    color: #fff;
    font-size: 11px;
    font-weight: 700;
}

.ingame-play__name {
    align-items: center;
    color: #fff;
    font-size: 20px;
    font-weight: 500;
    justify-content: center;
    letter-spacing: 1px;
    padding-left: 10px;
    width: 100%;
}

.ingame-play__control+.ingame-play__control {
    margin-left: 5px;
}

.ingame-play__control_change {
    width: 36px;
}

.ingame-play__control>img {
    height: 18px;
    transition: filter .2s ease-in-out;
    width: 18px;
}

.ingame-play__control_change>span {
    color: #fff;
    font-size: 16px;
    font-weight: 500;
    margin-bottom: 2px;
    transition: color .2s ease-in-out;
}
</style>
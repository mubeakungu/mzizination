<template>
    <div users="">
        <div class="loaders">
            <div class="lds-ellipsis">
                <div></div>
                <div></div>
                <div></div>
                <div></div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.loaders {
    align-items: center;
    display: flex;
    justify-content: center;
    min-height: 400px;
}
.lds-ellipsis {
    display: inline-block;
    height: 80px;
    position: relative;
    width: 80px;
}
.lds-ellipsis div {
    -webkit-animation-timing-function: cubic-bezier(0, 1, 1, 0);
    animation-timing-function: cubic-bezier(0, 1, 1, 0);
    background: #2053f4;
    border-radius: 50%;
    height: 13px;
    position: absolute;
    top: 33px;
    width: 13px;
}
.lds-ellipsis div:first-child {
    -webkit-animation: lds-ellipsis1 0.6s infinite;
    animation: lds-ellipsis1 0.6s infinite;
    left: 8px;
}
.lds-ellipsis div:nth-child(2) {
    left: 8px;
}
.lds-ellipsis div:nth-child(2),
.lds-ellipsis div:nth-child(3) {
    -webkit-animation: lds-ellipsis2 0.6s infinite;
    animation: lds-ellipsis2 0.6s infinite;
}
.lds-ellipsis div:nth-child(3) {
    left: 32px;
}
.lds-ellipsis div:nth-child(4) {
    -webkit-animation: lds-ellipsis3 0.6s infinite;
    animation: lds-ellipsis3 0.6s infinite;
    left: 56px;
}
@-webkit-keyframes lds-ellipsis1 {
    0% {
        transform: scale(0);
    }
    to {
        transform: scale(1);
    }
}
@keyframes lds-ellipsis1 {
    0% {
        transform: scale(0);
    }
    to {
        transform: scale(1);
    }
}
@-webkit-keyframes lds-ellipsis3 {
    0% {
        transform: scale(1);
    }
    to {
        transform: scale(0);
    }
}
@keyframes lds-ellipsis3 {
    0% {
        transform: scale(1);
    }
    to {
        transform: scale(0);
    }
}
@-webkit-keyframes lds-ellipsis2 {
    0% {
        transform: translate(0);
    }
    to {
        transform: translate(24px);
    }
}
@keyframes lds-ellipsis2 {
    0% {
        transform: translate(0);
    }
    to {
        transform: translate(24px);
    }
}

</style>
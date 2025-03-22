<div
    @class([
        'bg-white dark:bg-gray-800 overflow-hidden shadow-xl border-2 dark:border-gray-700 sm:rounded-lg min-w-[40%] mb-6 ml-6 max-w-4xl',
        'hidden' => !$fadeIn && !$fadeOut,
        'animate__animated animate__jackInTheBox' => $fadeIn,
        'animate__animated animate__fadeOutDown' => $fadeOut
    ])
>
    <div class="p-6 lg:p-8 bg-white dark:bg-gray-800 dark:bg-gradient-to-bl dark:from-gray-700/50 dark:via-transparent border-b border-gray-200 dark:border-gray-700">
        <div class="flex justify-start items-center gap-6">
            <img class="size-16 rounded-full object-cover" src="{{ $event?->avatar }}" alt="{{ $event?->nickname }}" />

            <h1 class="mt-8 text-2xl font-extrabold text-gray-900 dark:text-white" style="color: {{ $event?->color }}!important">
                {{ $event?->nickname }}
            </h1>
        </div>

        <p class="mt-6 text-gray-500 dark:text-gray-400 leading-relaxed">
            {{ $event?->message }}
        </p>
    </div>
</div>

@script
<script>

    let accessToken = '';
    let sessionId = '';

    const TWITCH_CLIENT_ID = '7np1trqon29ss5m984tqqylk9x16uh';
    const BROADCASTER_USER_ID = '57648209';
    const REWARD_ID = '944e2f9e-3937-4f96-abfe-8ad37374a823';

    async function getAccessToken() {
        try {
            const response = await axios.get('/auth/token');
            accessToken = response.data.accessToken;
            console.log('✅ Access token obtained');
        } catch (error) {
            console.error('❌ Failed to get access token:', error.message);
        }
    }

    async function refreshAccessToken() {
        try {
            const response = await axios.get('/auth/token/refresh');
            accessToken = response.data.accessToken;
            console.log('✅ Refreshed access token obtained');
        } catch (error) {
            console.error('❌ Failed to get access token:', error.message);
        }
    }

    async function connectWebSocket() {
        const ws = new WebSocket('wss://eventsub.wss.twitch.tv/ws');

        ws.onopen = () => console.log('✅ Connected to Twitch WebSocket');

        ws.onmessage = async (messageEvent) => {
            const message = JSON.parse(messageEvent.data);

            if (message.metadata?.message_type === 'session_welcome') {
                sessionId = message.payload.session.id;
                console.log(`📡 Session ID: ${sessionId}`);
                await subscribeToRewards();
            }

            if (message.metadata?.message_type === 'notification') {
                console.log('📡 Websocket Notification:', message);
                if (message.metadata?.subscription_type == 'channel.channel_points_custom_reward_redemption.add') {
                    handleRewardRedemption(message);
                } else if (message.metadata?.subscription_type == 'channel.follow') {
                    handleFollowEvent(message);
                }
            }
        };

        ws.onclose = () => {
            console.error('❌ WebSocket closed. Reconnecting in 10 seconds...');
            setTimeout(connectWebSocket, 10000);
        };

        ws.onerror = (error) => console.error('❌ WebSocket Error:', error);
    }

    async function subscribeToRewards() {
        try {
            let response = await attemptRewardSubscription();
            console.log('🎉 Subscribed to Channel Point Redemptions:', response.data);
            response = await attemptFollowSubscription();
            console.log('🎉 Subscribed to New Follower Events:', response.data);
        } catch (error) {
            console.error('❌ Subscription Error:', error.response?.data || error.message);
            if (error.response?.data.status == 401) {
                // Refresh token and retry
                console.log('Re-attempt subscription...');
                await refreshAccessToken();
                let response = await attemptSubscription();
                console.log('🎉 Subscribed to Channel Point Redemptions:', response.data);
                response = await attemptFollowSubscription();
                console.log('🎉 Subscribed to New Follower Events:', response.data);
            }
        }
    }

    function handleRewardRedemption(message) {
        console.log(`🎊 ${message.payload.event.user_name} redeemed: ${message.payload.event.reward.title}`, event);
        $wire.handleRewardEvent(message);
    }

    function handleFollowEvent(message) {
        console.log(`🎊 ${message.payload.event.user_name} just followed`, event);
        $wire.handleFollowEvent(message);
    }

    async function attemptRewardSubscription() {
        return axios.post(
            'https://api.twitch.tv/helix/eventsub/subscriptions',
            {
                type: 'channel.channel_points_custom_reward_redemption.add',
                version: '1',
                condition: { broadcaster_user_id: BROADCASTER_USER_ID, reward_id: REWARD_ID },
                transport: { method: 'websocket', session_id: sessionId }
            },
            {
                headers: {
                    'Client-ID': TWITCH_CLIENT_ID,
                    'Authorization': `Bearer ${accessToken}`,
                    'Content-Type': 'application/json'
                }
            }
        );
    }

    async function attemptFollowSubscription() {
        return axios.post(
            'https://api.twitch.tv/helix/eventsub/subscriptions',
            {
                type: 'channel.follow',
                version: '2',
                condition: { broadcaster_user_id: BROADCASTER_USER_ID, moderator_user_id: BROADCASTER_USER_ID },
                transport: { method: 'websocket', session_id: sessionId }
            },
            {
                headers: {
                    'Client-ID': TWITCH_CLIENT_ID,
                    'Authorization': `Bearer ${accessToken}`,
                    'Content-Type': 'application/json'
                }
            }
        );
    }
    let audio = new Audio();

    audio.addEventListener('ended', () => {
        $wire.dispatch('audio-player-ended');
    });

    $wire.on('play-audio', (event) => {
        // audio.pause();
        // audio.src = `data:audio/mpeg;base64,${event.base64Audio}`;
        // audio.play();
        setTimeout(() => {
            $wire.markAsPlayed();
        }, 10000);
    });

    $wire.on('audio-player-ended', () => {
        $wire.markAsPlayed();
    });

    // Start the bot when the page loads
    window.addEventListener('DOMContentLoaded', async () => {
        await getAccessToken();
        connectWebSocket();
    });
</script>
@endscript

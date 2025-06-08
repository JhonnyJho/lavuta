<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    user: Object,
    canPost: Boolean,
    posts: Array, 
});

const userId = props.user?.id ?? null;

const showForm = ref(false);
const selectedPost = ref(null);

const form = ref({
    title: '',
    body: '',
    image: null,
});

function submit() {
    const data = new FormData();
    data.append('title', form.value.title);
    data.append('body', form.value.body);
    if (form.value.image) data.append('image', form.value.image);

    router.post(route('posts.store', props.user.id), data, {
        onSuccess: () => {
            showForm.value = false;
            form.value = { title: '', body: '', image: null };
        }
    });
}


function rate(post, value) {
    router.post(route('posts.rate', post.id), { rating: value }, {
        preserveScroll: true,
        onSuccess: (page) => {
            // Update the post's rating info instantly
            const updated = page.props.posts?.find(p => p.id === post.id);
            if (updated) {
                post.avg_rating = updated.avg_rating;
                post.user_rating = updated.user_rating;
                post.ratings_count = updated.ratings_count;
            }
        }
    });
}

function unrate(post) {
    router.delete(route('posts.unrate', post.id), {
        preserveScroll: true,
        onSuccess: (page) => {
            // Update the post's rating info instantly
            const updated = page.props.posts?.find(p => p.id === post.id);
            if (updated) {
                post.avg_rating = updated.avg_rating;
                post.user_rating = updated.user_rating;
                post.ratings_count = updated.ratings_count;
            }
        }
    });
}


</script>

<template>
        <div class="flex flex-col items-center">
            <img
                :src="user.avatar ? ('/storage/' + user.avatar) : '/storage/avatars/defaultpfp.png'"
                class="w-24 h-24 rounded-full mb-4"
                alt="User Avatar"
            />
            <h2 class="text-xl font-bold mb-2">{{ user.name }}</h2>
            <p class="text-gray-600 mb-1">{{ user.email }}</p>
        </div>
    <div v-if="canPost" class="mb-6 text-center">
            <button
                class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700"
                @click="showForm = !showForm"
            >
                Make a new post
            </button>
        </div>
    <div v-if="showForm && canPost" class="mb-8 max-w-lg mx-auto bg-gray-100 p-4 rounded shadow">
        <form @submit.prevent="submit">
            <input
                v-model="form.title"
                type="text"
                placeholder="Title"
                class="block w-full mb-2 p-2 border rounded"
                required
            />
            <textarea
                v-model="form.body"
                placeholder="Write your post..."
                class="block w-full mb-2 p-2 border rounded"
                required
            ></textarea>
            <input
                type="file"
                accept="image/*"
                @change="e => form.image = e.target.files[0]"
                class="mb-2"
            />
            <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                Post
            </button>
        </form>
    </div>
    <div v-if="posts && posts.length" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
        <div
            v-for="post in posts"
            :key="post.id"
            class="bg-white rounded shadow p-4 cursor-pointer hover:shadow-lg transition"
            @click="selectedPost = post"
        >
            <h3 class="font-bold text-lg mb-2">{{ post.title }}</h3>
            <img
                v-if="post.image"
                :src="'/storage/' + post.image"
                class="w-full rounded mb-2"
                alt="Post Image"
            />
            <div class="flex items-center mt-2">
                <img
                    :src="post.user.avatar ? ('/storage/' + post.user.avatar) : '/storage/avatars/defaultpfp.png'"
                    class="w-6 h-6 rounded-full"
                    alt="Author Avatar"
                />
                <span class="text-sm text-blue-600 ml-2">{{ post.user.name }}</span>
            </div>

            <!-- Show stars and rating info -->
            <div class="flex items-center mb-2">
                <template v-for="star in 5">
                    <button
                        v-if="userId && post.user_id !== userId"
                        :key="star"
                        class="text-yellow-400 focus:outline-none"
                        :class="{'opacity-50': (post.user_rating ?? 0) < star}"
                        @click.stop="rate(post, star)"
                        :disabled="(post.user_id === userId) || (post.user_rating === star)"
                        title="Rate"
                    >★</button>
                    <span v-else :key="'star-else-' + star" class="text-yellow-200">★</span>
                </template>
                <span class="ml-2 text-sm text-gray-600">
                    {{ post.avg_rating ? post.avg_rating.toFixed(2) : '0.00' }} / 5
                    ({{ post.ratings_count ?? 0 }} votes)
                </span>
                <button
                    v-if="userId && post.user_rating && post.user_id !== userId"
                    class="ml-2 text-xs text-red-500 underline"
                    @click.stop="unrate(post)"
                >Cancel</button>
            </div>
        </div>
    </div>
    <div v-else class="text-center text-gray-400">No posts yet.</div>

    <div v-if="selectedPost" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded shadow-lg max-w-lg w-full p-6 relative">
            <button class="absolute top-2 right-2 text-gray-500 hover:text-black" @click="selectedPost = null">&times;</button>
            <h3 class="font-bold text-2xl mb-2">{{ selectedPost.title }}</h3>
            <img
                v-if="selectedPost.image"
                :src="'/storage/' + selectedPost.image"
                class="w-full rounded mb-4"
                alt="Post Image"
            />
            <p class="mb-4">{{ selectedPost.body }}</p>
            <div class="flex items-center">
                <img
                    :src="selectedPost.user.avatar ? ('/storage/' + selectedPost.user.avatar) : '/storage/avatars/defaultpfp.png'"
                    class="w-6 h-6 rounded-full"
                    alt="Author Avatar"
                />
                <span class="text-sm text-blue-600 ml-2">{{ selectedPost.user.name }}</span>
            </div>
        </div>
    </div>
</template>
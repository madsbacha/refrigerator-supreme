<template>
    <form class="flex container m-5" v-on:submit.prevent="submit">
        <div class="p-5 w-1/2">
            <div class="flex justify-between mb-5">
                <div class="flex flex-col flex-grow w-2/3">
                    <label for="name" class="label">Drink Name</label>
                    <input type="text" v-model="name" id="name" class="input" placeholder="Monster Energy" required>
                </div>
                <div class="flex flex-col flex-grow ml-8">
                    <label for="price" class="label">Price</label>
                    <input type="number" v-model="price" id="price" class="input" placeholder="12,00 kr." required>
                </div>
            </div>
            <div class="flex justify=between mb-5">
                <div class="flex flex-col flex-grow w-2/3">
                    <label for="energy" class="label">Energy per 100 ml</label>
                    <input type="number" v-model="energy" id="energy" class="input" placeholder="487 kJ" required>
                </div>
                <div class="flex flex-col flex-grow ml-8">
                    <label for="mlSize" class="label">Milliliter</label>
                    <input type="number" v-model="size" id="mlSize" class="input" placeholder="250ml" required>
                </div>
            </div>
            <div class="flex mb-10">
                <div class="flex flex-col flex-grow">
                    <label for="imageUrl" class="label">Image URL</label>
                    <input type="text" v-model="imageURL" class="input" id="imageUrl" placeholder="website.com/cool-drink.png" required>
                    <label class="error-label">{{ imageError }}</label>
                </div>
            </div>
            <!--<div class="flex mb-10">
                <div class="flex flex-col">
                    <label for="image-file" class="label">Choose an image</label>
                    <input type="file" id="image-file" accept="image/*">
                </div>
            </div>-->
            <div class="flex">
                <button type="submit" class="btn-submit w-1/2 hover:bg-blue-700">{{ submitButtonText }}</button>
                <button type="button" class="btn-secondary" @click="resetForm">Clear</button>
            </div>
        </div>
        <div class="p-5 w-1/2">
            <div>
                <div class="list-item" v-for="item in items" :key="item.id">
                    <label>{{ item.name }}</label>
                    <button class="px-2 float-right" type="button" v-on:click="deleteItem(item.id)">
                        <font-awesome-icon icon="trash"/>
                    </button>
                    <button class="px-3 float-right" type="button" v-on:click="editItem(item)">
                        Edit
                    </button>
                </div>
            </div>
        </div>
    </form>
</template>

<script>
import RETRIEVE_ITEMS from '../../graphql/Items.gql'
import CREATE_ITEM_MUTATION from '../../graphql/CreateItem.gql'
import DELETE_ITEM_MUTATION from '../../graphql/DeleteItem.gql'

export default {
    name: 'New',
    data: () => ({
        name: '',
        price: '',
        energy: '',
        size: '',
        imageURL: '',
        items: [],
        submitButtonText: 'Add Item',
        editing: null,
        imageError: ''
    }),
    methods: {
        submit () {
            try {
                this.validateImageExtension()
            } catch (e) {
                this.imageError = e.message
                return
            }
            this.submitButtonText = 'Add Item'
            this.$apollo.mutate({
                mutation: CREATE_ITEM_MUTATION,
                variables: {
                    name: this.name,
                    image: this.imageURL,
                    price: this.price,
                    energy: this.energy,
                    size: this.size
                }
            }).then(res => {
                if (res['data']['CreateItem']) {
                    this.items.push(res['data']['CreateItem'])
                }
            })
        },
        deleteItem (delId) {
            this.$apollo.mutate({
                mutation: DELETE_ITEM_MUTATION,
                variables: {
                    id: delId
                }
            }).then(res => {
                if (res['data']['DeleteItem']['success']) {
                    const itemsIndex = this.items.findIndex(item => item.id === delId)
                    this.items.splice(itemsIndex, 1)
                }
            })
        },
        resetForm () {
            this.name = ''
            this.price = ''
            this.energy = ''
            this.size = ''
            this.imageURL = ''
            this.submitButtonText = 'Add Item'
        },
        editItem (item) {
            this.editing = item
            this.name = item['name']
            this.price = item['price']
            this.energy = item['energy']
            this.size = item['size']
            this.imageURL = item['image']
            this.submitButtonText = 'Submit Changes'
        },
        validateImageExtension () {
            let extension = this.imageURL.split('.').pop()
            if (extension.toLowerCase() !== 'png') {
                throw new Error('The linked image is not a PNG. \nTake your shit image somewhere else.')
            } else {
                this.imageError = ''
            }
        }
    },
    apollo: {
        items: RETRIEVE_ITEMS
    }
}
</script>

<style scoped>

</style>

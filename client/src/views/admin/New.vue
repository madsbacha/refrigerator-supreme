<template>
    <div class="container">
        <div class="p-5 w-1/2">
            <div class="flex justify-between mb-5">
                <div class="flex flex-col flex-grow w-2/3">
                    <label for="name" class="label">Drink Name</label>
                    <input type="text" v-model="name" id="name" class="px-3 py-2 bg-gray-200 rounded shadow"
                           placeholder="Monster Energy">
                </div>
                <div class="flex flex-col flex-grow ml-8">
                    <label for="price" class="label">Price</label>
                    <input type="text" id="price" class="input" placeholder="12,00 kr.">
                </div>
            </div>
            <div class="flex justify=between mb-5">
                <div class="flex flex-col flex-grow w-2/3">
                    <label for="energy" class="label">Energy per 100 ml</label>
                    <input type="number" id="energy" class="input" placeholder="487 kJ">
                </div>
                <div class="flex flex-col flex-grow ml-8">
                    <label for="content" class="label">Milliliter</label>
                    <input type="number" id="content" class="input" placeholder="250ml">
                </div>
            </div>
            <div class="flex mb-10">
                <div class="flex flex-col">
                    <label for="image-file">Choose an image</label>
                    <input type="file" id="image-file">
                </div>
            </div>
            <div class="flex">
                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded w-1/2">Add Drink</button>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'New',
    data: () => ({
        name: '',
        price: '',
        energy: ''
    })
}
</script>

<style scoped>

</style>

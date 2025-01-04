<script lang="ts">
import axios from 'axios'
import { defineProps } from 'vue'

const props = defineProps({
  job: Object,
})

export async function fetchWeather({ lat, lng, start_date, end_date, job_id = null }) {
  try {
    console.log('Fetching weather with params:', { lat, lng, start_date, end_date, job_id }) 
    const response = await axios.get('/api/weather', {
      params: {
        lat,
        lng,
        start_date,
        end_date,
        job_id,
      },
    })
    console.log('Weather response:', response.data) 
    return response.data
  }
  catch (error) {
    console.error('Error fetching weather data', error.response?.data || error) 
    return null
  }
}
</script>

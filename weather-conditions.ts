// resources/js/config/weather-conditions.ts

interface WeatherCondition {
    id: number
    description: string
    icon: string
    group: string
  }
  
  export const weatherConditions: WeatherCondition[] = [
      {
          id: 200,
          description: 'thunderstorm with light rain',
          icon: '11d',
          group: 'Thunderstorm'
      },
      {
          id: 201,
          description: 'thunderstorm with rain',
          icon: '11d',
          group: 'Thunderstorm'
      },
      {
          id: 202,
          description: 'thunderstorm with heavy rain',
          icon: '11d',
          group: 'Thunderstorm'
      },
      {
          id: 210,
          description: 'light thunderstorm',
          icon: '11d',
          group: 'Thunderstorm'
      },
      {
          id: 211,
          description: 'thunderstorm',
          icon: '11d',
          group: 'Thunderstorm'
      },
      {
          id: 212,
          description: 'heavy thunderstorm',
          icon: '11d',
          group: 'Thunderstorm'
      },
      {
          id: 221,
          description: 'ragged thunderstorm',
          icon: '11d',
          group: 'Thunderstorm'
      },
      {
          id: 230,
          description: 'thunderstorm with light drizzle',
          icon: '11d',
          group: 'Thunderstorm'
      },
      {
          id: 231,
          description: 'thunderstorm with drizzle',
          icon: '11d',
          group: 'Thunderstorm'
      },
      {
          id: 232,
          description: 'thunderstorm with heavy drizzle',
          icon: '11d',
          group: 'Thunderstorm'
      },
      {
          id: 300,
          description: 'light intensity drizzle',
          icon: '09d',
          group: 'Drizzle'
      },
      {
          id: 301,
          description: 'drizzle',
          icon: '09d',
          group: 'Drizzle'
      },
      {
          id: 302,
          description: 'heavy intensity drizzle',
          icon: '09d',
          group: 'Drizzle'
      },
      {
          id: 310,
          description: 'light intensity drizzle rain',
          icon: '09d',
          group: 'Drizzle'
      },
      {
          id: 311,
          description: 'drizzle rain',
          icon: '09d',
          group: 'Drizzle'
      },
      {
          id: 312,
          description: 'heavy intensity drizzle rain',
          icon: '09d',
          group: 'Drizzle'
      },
      {
          id: 313,
          description: 'shower rain and drizzle',
          icon: '09d',
          group: 'Drizzle'
      },
      {
          id: 314,
          description: 'heavy shower rain and drizzle',
          icon: '09d',
          group: 'Drizzle'
      },
      {
          id: 321,
          description: 'shower drizzle',
          icon: '09d',
          group: 'Drizzle'
      },
      {
          id: 500,
          description: 'light rain',
          icon: '10d',
          group: 'Rain'
      },
      {
          id: 501,
          description: 'moderate rain',
          icon: '10d',
          group: 'Rain'
      },
      {
          id: 502,
          description: 'heavy intensity rain',
          icon: '10d',
          group: 'Rain'
      },
      {
          id: 503,
          description: 'very heavy rain',
          icon: '10d',
          group: 'Rain'
      },
      {
          id: 504,
          description: 'extreme rain',
          icon: '10d',
          group: 'Rain'
      },
      {
          id: 511,
          description: 'freezing rain',
          icon: '13d',
          group: 'Rain'
      },
      {
          id: 520,
          description: 'light intensity shower rain',
          icon: '09d',
          group: 'Rain'
      },
      {
          id: 521,
          description: 'shower rain',
          icon: '09d',
          group: 'Rain'
      },
      {
          id: 522,
          description: 'heavy intensity shower rain',
          icon: '09d',
          group: 'Rain'
      },
      {
          id: 531,
          description: 'ragged shower rain',
          icon: '09d',
          group: 'Rain'
      },
      {
          id: 600,
          description: 'light snow',
          icon: '13d',
          group: 'Snow'
      },
      {
          id: 601,
          description: 'snow',
          icon: '13d',
          group: 'Snow'
      },
      {
          id: 602,
          description: 'heavy snow',
          icon: '13d',
          group: 'Snow'
      },
      {
          id: 611,
          description: 'sleet',
          icon: '13d',
          group: 'Snow'
      },
      {
          id: 612,
          description: 'light shower sleet',
          icon: '13d',
          group: 'Snow'
      },
      {
          id: 613,
          description: 'shower sleet',
          icon: '13d',
          group: 'Snow'
      },
      {
          id: 615,
          description: 'light rain and snow',
          icon: '13d',
          group: 'Snow'
      },
      {
          id: 616,
          description: 'rain and snow',
          icon: '13d',
          group: 'Snow'
      },
      {
          id: 620,
          description: 'light shower snow',
          icon: '13d',
          group: 'Snow'
      },
      {
          id: 621,
          description: 'shower snow',
          icon: '13d',
          group: 'Snow'
      },
      {
          id: 622,
          description: 'heavy shower snow',
          icon: '13d',
          group: 'Snow'
      },
      {
          id: 701,
          description: 'mist',
          icon: '50d',
          group: 'Atmosphere'
      },
      {
          id: 711,
          description: 'smoke',
          icon: '50d',
          group: 'Atmosphere'
      },
      {
          id: 721,
          description: 'haze',
          icon: '50d',
          group: 'Atmosphere'
      },
      {
          id: 731,
          description: 'sand/dust whirls',
          icon: '50d',
          group: 'Atmosphere'
      },
      {
          id: 741,
          description: 'fog',
          icon: '50d',
          group: 'Atmosphere'
      },
      {
          id: 751,
          description: 'sand',
          icon: '50d',
          group: 'Atmosphere'
      },
      {
          id: 761,
          description: 'dust',
          icon: '50d',
          group: 'Atmosphere'
      },
      {
          id: 762,
          description: 'volcanic ash',
          icon: '50d',
          group: 'Atmosphere'
      },
      {
          id: 771,
          description: 'squalls',
          icon: '50d',
          group: 'Atmosphere'
      },
      {
          id: 781,
          description: 'tornado',
          icon: '50d',
          group: 'Atmosphere'
      },
      {
          id: 800,
          description: 'clear sky',
          icon: '01d',
          group: 'Clear'
      },
      {
          id: 801,
          description: 'few clouds (11-25%)',
          icon: '02d',
          group: 'Clouds'
      },
      {
          id: 802,
          description: 'scattered clouds (25-50%)',
          icon: '03d',
          group: 'Clouds'
      },
      {
          id: 803,
          description: 'broken clouds (51-84%)',
          icon: '04d',
          group: 'Clouds'
      },
      {
          id: 804,
          description: 'overcast clouds (85-100%)',
          icon: '04d',
          group: 'Clouds'
      }
  ]
  
  export default weatherConditions

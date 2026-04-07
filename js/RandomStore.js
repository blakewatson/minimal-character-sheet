'use strict';

class RandomStore {
  constructor() {
    this.store = this.loadJSON('randomstore') || {};
    this.siloThreshold = 50;
    this.siloCapacity = 100;
    this.makingRequest = false;
    this.apiKey = import.meta.env.VITE_RANDOM_ORG_API_KEY;
  }

  loadJSON(key) {
    const raw = localStorage.getItem(key);
    if (!raw) return null;
    try {
      return JSON.parse(raw);
    } catch {
      return null;
    }
  }

  saveJSON(key, value) {
    localStorage.setItem(key, JSON.stringify(value));
  }

  get(sides) {
    const silo = this.getSilo(sides);

    if (!silo || silo.length < 1) {
      return Math.floor(Math.random() * sides) + 1;
    }

    const num = silo.shift();
    this.saveJSON('randomstore', this.store);
    this.fillSilo(silo, sides);
    return num;
  }

  getSilo(sides) {
    if (this.store.hasOwnProperty(sides.toString())) {
      return this.store[sides.toString()];
    }

    this.makeSilo(sides);
    return false;
  }

  makeSilo(sides) {
    const silo = [];
    this.store[sides] = silo;
    this.fillSilo(silo, sides);
  }

  // top off silo to 100 if it has under 50
  fillSilo(silo, sides) {
    if (silo.length > this.siloThreshold || !this.apiKey) {
      return;
    }

    const amount = this.siloCapacity - silo.length;

    if (this.makingRequest) {
      return;
    }

    this.makingRequest = true;

    fetch('https://api.random.org/json-rpc/2/invoke', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        jsonrpc: '2.0',
        method: 'generateIntegers',
        params: {
          apiKey: this.apiKey,
          n: amount,
          min: 1,
          max: sides,
          replacement: true,
          base: 10,
        },
        id: 1,
      }),
    })
      .then((response) => response.json())
      .then((data) => {
        this.store[sides] = this.store[sides].concat(data.result.random.data);
        this.makingRequest = false;
        this.saveJSON('randomstore', this.store);
      });
  }
}

export default new RandomStore();
